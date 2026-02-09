<?php

namespace App\Actions\Payment;

use App\Models\Registration;
use Illuminate\Support\Facades\DB;

class GenerateBibNumberAction
{
    /**
     * Generate and assign BIB numbers to all participants in registration
     * BIB numbers are sequential within the category's BIB range
     *
     * @throws \Exception if BIB range exhausted
     */
    public function execute(Registration $registration): void
    {
        DB::transaction(function () use ($registration) {
            $category = $registration->raceCategory;

            // Get participants without BIB number
            $participants = $registration->participants()
                ->whereNull('bib_number')
                ->get();

            if ($participants->isEmpty()) {
                return; // Already have BIB numbers
            }

            // Find next available BIB number
            $lastAssignedBib = DB::table('participants')
                ->join('registrations', 'participants.registration_id', '=', 'registrations.id')
                ->where('registrations.race_category_id', $category->id)
                ->whereNotNull('participants.bib_number')
                ->max('participants.bib_number');

            $nextBibNumber = $lastAssignedBib
                ? $lastAssignedBib + 1
                : $category->bib_start_number;

            // Check if we have enough BIB numbers in range
            $requiredCount = $participants->count();
            $availableCount = $category->bib_end_number - $nextBibNumber + 1;

            if ($availableCount < $requiredCount) {
                throw new \Exception(
                    "BIB range exhausted for category {$category->name}. Required: {$requiredCount}, Available: {$availableCount}"
                );
            }

            // Assign BIB numbers
            foreach ($participants as $participant) {
                $participant->update([
                    'bib_number' => $nextBibNumber++,
                ]);
            }
        });
    }

    /**
     * Check if BIB numbers are available for a category
     */
    public function hasAvailableBibNumbers(Registration $registration, int $count): bool
    {
        $category = $registration->raceCategory;

        $lastAssignedBib = DB::table('participants')
            ->join('registrations', 'participants.registration_id', '=', 'registrations.id')
            ->where('registrations.race_category_id', $category->id)
            ->whereNotNull('participants.bib_number')
            ->max('participants.bib_number');

        $nextBibNumber = $lastAssignedBib
            ? $lastAssignedBib + 1
            : $category->bib_start_number;

        $availableCount = $category->bib_end_number - $nextBibNumber + 1;

        return $availableCount >= $count;
    }
}
