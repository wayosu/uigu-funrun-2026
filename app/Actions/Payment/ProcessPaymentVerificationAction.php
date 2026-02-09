<?php

namespace App\Actions\Payment;

use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ProcessPaymentVerificationAction
{
    public function __construct(
        private GenerateBibNumberAction $generateBibNumberAction,
    ) {}

    /**
     * Verify payment and generate BIB numbers
     */
    public function execute(
        Payment $payment,
        User $verifier,
        bool $approved,
        ?string $rejectionReason = null
    ): void {
        DB::transaction(function () use ($payment, $verifier, $approved, $rejectionReason) {
            $registration = $payment->registration;

            if ($approved) {
                // Approve payment
                $payment->update([
                    'verified_at' => now(),
                    'verified_by' => $verifier->id,
                    'rejection_reason' => null,
                ]);

                // Update registration status
                $registration->update([
                    'status' => PaymentStatus::PaymentVerified,
                    'payment_verified_at' => now(),
                    'payment_verified_by' => $verifier->id,
                ]);

                // Generate BIB numbers for all participants
                $this->generateBibNumberAction->execute($registration);

                // Fire PaymentVerified event (to be created)
                // event(new PaymentVerified($registration));
            } else {
                // Reject payment
                $payment->update([
                    'verified_at' => null,
                    'verified_by' => null,
                    'rejection_reason' => $rejectionReason,
                ]);

                // Revert registration status to pending
                $registration->update([
                    'status' => PaymentStatus::PendingPayment,
                    'payment_verified_at' => null,
                    'payment_verified_by' => null,
                ]);

                // Delete payment record to allow re-upload
                $payment->delete();

                // Fire PaymentRejected event (to be created)
                // event(new PaymentRejected($registration, $rejectionReason));
            }
        });
    }
}
