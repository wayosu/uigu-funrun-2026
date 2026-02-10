<?php

namespace App\Actions\Registration;

use App\Enums\PaymentStatus;
use App\Enums\RegistrationType;
use App\Models\RaceCategory;
use App\Models\Registration;
use Illuminate\Support\Facades\DB;

class CreateRegistrationAction
{
    public function __construct(
        private GenerateRegistrationNumberAction $generateNumberAction,
        private ValidateAvailableSlotsAction $validateSlotsAction,
    ) {}

    /**
     * Create registration with participants
     *
     * @param array{
     *   race_category_id: int,
     *   registration_type: RegistrationType,
     *   participants: array,
     *   total_amount: float
     * } $data
     */
    public function execute(array $data): Registration
    {
        return DB::transaction(function () use ($data) {
            /** @var RaceCategory $category */
            $category = RaceCategory::findOrFail($data['race_category_id']);

            /** @var RegistrationType $registrationType */
            $registrationType = $data['registration_type'];
            $participantsData = $data['participants'];
            $participantsCount = count($participantsData);

            // Validate expected participants count
            if ($participantsCount !== $registrationType->participantsCount()) {
                throw new \Exception(
                    "Invalid participants count. Expected: {$registrationType->participantsCount()}, Got: {$participantsCount}"
                );
            }

            // Validate available slots
            $this->validateSlotsAction->execute($category, $participantsCount);

            // Generate registration number
            $registrationNumber = $this->generateNumberAction->execute($category);

            // Calculate expired_at (24 hours from now)
            $paymentSettings = \App\Models\PaymentSetting::first();
            $deadlineHours = $paymentSettings?->payment_deadline_hours ?? 24;
            $expiredAt = now()->addHours($deadlineHours);

            // Create registration
            $picData = $participantsData[0];
            $registration = Registration::create([
                'race_category_id' => $category->id,
                'registration_number' => $registrationNumber,
                'registration_type' => $registrationType,
                'participants_count' => $participantsCount,
                'pic_name' => $picData['full_name'],
                'pic_email' => $picData['email'],
                'pic_phone' => $picData['whatsapp'],
                'total_amount' => $data['total_amount'],
                'status' => PaymentStatus::PendingPayment,
                'expired_at' => $expiredAt,
            ]);

            // Create participants
            foreach ($participantsData as $index => $participantData) {
                $registration->participants()->create([
                    'is_pic' => $participantData['is_pic'] ?? ($index === 0),
                    'name' => $participantData['full_name'],
                    'bib_name' => $participantData['bib_name'] ?? null,
                    'email' => $participantData['email'],
                    'phone' => $participantData['whatsapp'],
                    'gender' => $participantData['gender'],
                    'birth_date' => $participantData['date_of_birth'],
                    'jersey_size' => $participantData['jersey_size'],
                    'identity_number' => $participantData['identity_number'] ?? null,
                    'blood_type' => $participantData['blood_type'] ?? null,
                    'emergency_name' => $participantData['emergency_contact_name'],
                    'emergency_phone' => $participantData['emergency_contact_phone'],
                    'emergency_relation' => $participantData['emergency_relation'],
                ]);
            }

            return $registration->load('participants');
        });
    }
}
