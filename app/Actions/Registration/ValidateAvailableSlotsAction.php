<?php

namespace App\Actions\Registration;

use App\Models\RaceCategory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ValidateAvailableSlotsAction
{
    /**
     * Check if race category has available slots for given participant count
     *
     * @throws \Exception if slots not available
     */
    public function execute(RaceCategory $category, int $participantsCount): void
    {
        DB::transaction(function () use ($category, $participantsCount) {
            // Lock category for quota check
            $categoryLocked = RaceCategory::query()
                ->where('id', $category->id)
                ->lockForUpdate()
                ->first();

            // Calculate current participants count
            $currentParticipants = DB::table('participants')
                ->join('registrations', 'participants.registration_id', '=', 'registrations.id')
                ->where('registrations.race_category_id', $categoryLocked->id)
                ->whereIn('registrations.status', ['pending_payment', 'payment_uploaded', 'payment_verified'])
                ->count();

            // Check available slots
            $availableSlots = $categoryLocked->quota - $currentParticipants;

            if ($availableSlots < $participantsCount) {
                throw new \Exception(
                    "Not enough slots available. Requested: {$participantsCount}, Available: {$availableSlots}"
                );
            }
        });
    }

    /**
     * Get available slots count without throwing exception
     */
    public function getAvailableSlots(RaceCategory $category): int
    {
        // Cache available slots for 5 minutes to reduce database load
        return Cache::remember(
            "available_slots_{$category->id}",
            now()->addMinutes(5),
            function () use ($category) {
                $currentParticipants = DB::table('participants')
                    ->join('registrations', 'participants.registration_id', '=', 'registrations.id')
                    ->where('registrations.race_category_id', $category->id)
                    ->whereIn('registrations.status', ['pending_payment', 'payment_uploaded', 'payment_verified'])
                    ->count();

                return max(0, $category->quota - $currentParticipants);
            }
        );
    }

    /**
     * Clear cached available slots for a category
     */
    public function clearCache(RaceCategory $category): void
    {
        Cache::forget("available_slots_{$category->id}");
    }
}
