<?php

namespace App\Services;

use App\Actions\Registration\CreateRegistrationAction;
use App\Actions\Registration\ValidateAvailableSlotsAction;
use App\Enums\RegistrationType;
use App\Models\RaceCategory;
use App\Models\Registration;

class RegistrationService
{
    public function __construct(
        private CreateRegistrationAction $createRegistrationAction,
        private ValidateAvailableSlotsAction $validateSlotsAction,
    ) {}

    /**
     * Create individual registration
     */
    public function createIndividualRegistration(RaceCategory $category, array $participantData): Registration
    {
        $data = [
            'race_category_id' => $category->id,
            'registration_type' => RegistrationType::Individual,
            'total_amount' => $category->price_individual,
            'participants' => [
                array_merge($participantData, ['is_pic' => true]),
            ],
        ];

        $registration = $this->createRegistrationAction->execute($data);

        // Fire event
        event(new \App\Events\RegistrationCreated($registration));

        return $registration;
    }

    /**
     * Create collective registration (5 or 10 people)
     */
    public function createCollectiveRegistration(
        RaceCategory $category,
        RegistrationType $registrationType,
        array $picData,
        array $membersData
    ): Registration {
        // Validate registration type is collective
        if (! in_array($registrationType, [RegistrationType::Collective5, RegistrationType::Collective10])) {
            throw new \Exception('Invalid registration type for collective registration');
        }

        // Get price based on type
        $price = match ($registrationType) {
            RegistrationType::Collective5 => $category->price_collective_5,
            RegistrationType::Collective10 => $category->price_collective_10,
            default => throw new \Exception('Invalid collective type'),
        };

        if (! $price) {
            throw new \Exception("Collective registration type {$registrationType->value} not available for this category");
        }

        // Prepare participants array (PIC + members)
        $participants = [
            array_merge($picData, ['is_pic' => true]),
            ...array_map(fn ($member) => array_merge($member, ['is_pic' => false]), $membersData),
        ];

        $data = [
            'race_category_id' => $category->id,
            'registration_type' => $registrationType,
            'total_amount' => $price,
            'participants' => $participants,
        ];

        $registration = $this->createRegistrationAction->execute($data);

        // Fire event
        event(new \App\Events\RegistrationCreated($registration));

        return $registration;
    }

    /**
     * Get available slots for a category
     */
    public function getAvailableSlots(RaceCategory $category): int
    {
        return $this->validateSlotsAction->getAvailableSlots($category);
    }

    /**
     * Check if category is available for registration
     */
    public function isCategoryAvailable(RaceCategory $category): bool
    {
        // Check if category is active
        if (! $category->is_active) {
            return false;
        }

        // Check if event is active
        if (! $category->event->is_active) {
            return false;
        }

        // Check registration window
        if ($category->registration_open_at && now()->isBefore($category->registration_open_at)) {
            return false;
        }

        if ($category->registration_close_at && now()->isAfter($category->registration_close_at)) {
            return false;
        }

        // Check available slots
        if ($this->getAvailableSlots($category) <= 0) {
            return false;
        }

        return true;
    }

    /**
     * Get registration by number
     */
    public function findByRegistrationNumber(string $registrationNumber): ?Registration
    {
        return Registration::query()
            ->where('registration_number', $registrationNumber)
            ->with(['raceCategory.event', 'participants', 'payments'])
            ->first();
    }

    /**
     * Check if registration can upload payment
     */
    public function canUploadPayment(Registration $registration): bool
    {
        return $registration->status->canUploadPayment()
            && ! $registration->isExpired();
    }
}
