<?php

namespace App\Http\Controllers;

use App\Enums\PaymentMethod;
use App\Http\Requests\PaymentUploadRequest;
use App\Models\Registration;
use App\Services\PaymentService;
use App\Services\RegistrationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function __construct(
        private PaymentService $paymentService,
        private RegistrationService $registrationService,
    ) {}

    /**
     * Show payment page
     */
    public function show(Registration $registration): View|RedirectResponse
    {
        // If already paid, redirect to status page
        if ($registration->isPaid()) {
            return redirect()->route('payment.status', $registration->registration_number);
        }

        // Check if expired
        if ($registration->isExpired()) {
            return view('payment.expired', compact('registration'));
        }

        $paymentSettings = $this->paymentService->getPaymentSettings();

        return view('payment.show', compact('registration', 'paymentSettings'));
    }

    /**
     * Upload payment proof
     */
    public function store(PaymentUploadRequest $request, Registration $registration): RedirectResponse
    {
        try {
            // Validate can upload payment
            if (! $this->registrationService->canUploadPayment($registration)) {
                return back()->withErrors([
                    'error' => 'Cannot upload payment at this time',
                ]);
            }

            $paymentMethod = PaymentMethod::from($request->input('payment_method'));
            $proofFile = $request->file('payment_proof');
            $notes = $request->input('notes');

            $this->paymentService->uploadPaymentProof(
                $registration,
                $paymentMethod,
                $proofFile,
                $notes
            );

            return redirect()
                ->route('payment.status', $registration->registration_number)
                ->with('success', 'Payment proof uploaded successfully! We will verify your payment within 24 hours.');

        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Show payment status
     */
    public function status(Registration $registration): View
    {
        $registration->load([
            'participants',
            'payments' => fn ($query) => $query->latest(),
            'raceCategory.event',
        ]);

        return view('payment.status', compact('registration'));
    }
}
