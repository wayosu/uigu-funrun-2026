<?php

namespace App\Services;

use App\Actions\Payment\ProcessPaymentVerificationAction;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class PaymentService
{
    public function __construct(
        private ProcessPaymentVerificationAction $processVerificationAction,
    ) {}

    /**
     * Upload payment proof
     */
    public function uploadPaymentProof(
        Registration $registration,
        PaymentMethod $paymentMethod,
        UploadedFile $proofFile,
        ?string $notes = null
    ): Payment {
        // Validate registration can upload payment
        if (! $registration->status->canUploadPayment()) {
            throw new \Exception("Cannot upload payment for registration with status: {$registration->status->label()}");
        }

        // Check if payment already verified - cannot re-upload
        if ($registration->status === PaymentStatus::PaymentVerified) {
            throw new \Exception('Payment already verified. Cannot upload new proof.');
        }

        // Store file in local storage (storage/app/private)
        $path = $proofFile->store('payment-proofs', 'local');

        // Get or create payment record
        $payment = $registration->payments()->first();

        if ($payment) {
            // Update existing payment record (allow re-upload for pending/uploaded status)
            $payment->update([
                'amount' => $registration->total_amount,
                'proof_path' => $path,
                'rejection_reason' => null, // Clear rejection reason if re-uploading
            ]);
        } else {
            // Create new payment record
            $payment = $registration->payments()->create([
                'amount' => $registration->total_amount,
                'proof_path' => $path,
            ]);
        }

        // Update registration status
        $registration->update([
            'status' => PaymentStatus::PaymentUploaded,
        ]);

        // Keep uploaded event for backward-compatible two-step notification flow.
        event(new \App\Events\PaymentUploaded($registration, $payment));

        // Auto-verify immediately after upload based on business decision.
        $this->processVerificationAction->execute($payment, null, true);

        $registration->refresh();
        $payment->refresh();

        event(new \App\Events\PaymentVerified($registration, $payment));

        return $payment;
    }

    /**
     * Verify payment (approve or reject)
     */
    public function verifyPayment(
        Payment $payment,
        User $verifier,
        bool $approved,
        ?string $rejectionReason = null
    ): void {
        $registration = $payment->registration;

        if ($approved) {
            if (! $registration->status->canBeVerified()) {
                throw new \Exception("Cannot verify payment for registration with status: {$registration->status->label()}");
            }

            if (! empty($rejectionReason)) {
                throw new \Exception('Invalid verification parameters');
            }

            $this->processVerificationAction->execute($payment, $verifier, true);

            // Fire event
            event(new \App\Events\PaymentVerified($registration, $payment));

            return;
        }

        if (empty($rejectionReason)) {
            throw new \Exception('Invalid verification parameters');
        }

        $allowedStatusesForRejection = [
            PaymentStatus::PaymentUploaded,
            PaymentStatus::PaymentVerified,
        ];

        if (! in_array($registration->status, $allowedStatusesForRejection, true)) {
            throw new \Exception("Cannot reject payment for registration with status: {$registration->status->label()}");
        }

        if (! $approved) {
            $this->processVerificationAction->execute($payment, $verifier, false, $rejectionReason);

            // Fire event
            event(new \App\Events\PaymentRejected($registration, $rejectionReason));

            return;
        }

        throw new \Exception('Invalid verification parameters');
    }

    /**
     * Get payment settings
     */
    public function getPaymentSettings()
    {
        return \App\Models\PaymentSetting::where('is_active', true)->get();
    }

    /**
     * Get payment proof file path (for private storage)
     */
    public function getPaymentProofPath(Payment $payment): string
    {
        if (! Storage::disk('local')->exists($payment->proof_path)) {
            throw new \Exception('Payment proof file not found');
        }

        return Storage::disk('local')->path($payment->proof_path);
    }

    /**
     * Delete payment proof (when rejected or cancelled)
     */
    public function deletePaymentProof(Payment $payment): void
    {
        if (Storage::disk('local')->exists($payment->proof_path)) {
            Storage::disk('local')->delete($payment->proof_path);
        }

        $payment->delete();
    }
}
