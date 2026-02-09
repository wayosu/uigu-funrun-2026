<?php

namespace App\Http\Controllers;

use App\Enums\RegistrationType;
use App\Http\Requests\RegistrationRequest;
use App\Models\RaceCategory;
use App\Services\RegistrationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RegistrationController extends Controller
{
    public function __construct(
        private RegistrationService $registrationService
    ) {}

    /**
     * Show registration form for a specific category
     */
    public function show(RaceCategory $category): View
    {
        // Check if category is available
        if (! $this->registrationService->isCategoryAvailable($category)) {
            abort(404, 'Category is not available for registration');
        }

        $availableSlots = $this->registrationService->getAvailableSlots($category);

        // Get available jersey sizes (global + category-specific)
        $jerseySizes = \App\Models\JerseySize::query()
            ->active()
            ->forRaceCategory($category->id)
            ->ordered()
            ->get();

        return view('registration.form', compact('category', 'availableSlots', 'jerseySizes'));
    }

    /**
     * Store registration
     */
    public function store(RegistrationRequest $request, RaceCategory $category): RedirectResponse
    {
        try {
            $registrationType = RegistrationType::from($request->input('registration_type'));

            // Create registration based on type
            if ($registrationType === RegistrationType::Individual) {
                $registration = $this->registrationService->createIndividualRegistration(
                    $category,
                    $request->input('pic')
                );
            } else {
                // Collective registration
                $registration = $this->registrationService->createCollectiveRegistration(
                    $category,
                    $registrationType,
                    $request->input('pic'),
                    $request->input('members', [])
                );
            }

            return redirect()
                ->route('payment.show', $registration->registration_number)
                ->with('success', 'Registration successful! Please complete your payment.');

        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }
}
