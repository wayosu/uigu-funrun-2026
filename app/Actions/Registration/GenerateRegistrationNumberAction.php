<?php

namespace App\Actions\Registration;

use App\Models\RaceCategory;
use App\Models\RegistrationSequence;
use Illuminate\Support\Facades\DB;

class GenerateRegistrationNumberAction
{
    /**
     * Generate unique registration number for a race category
     * Format: {PREFIX}-{SEQUENCE}
     * Example: FR5K-0001
     */
    public function execute(RaceCategory $category): string
    {
        return DB::transaction(function () use ($category) {
            // Lock sequence record for atomic increment
            $sequence = RegistrationSequence::query()
                ->where('race_category_id', $category->id)
                ->lockForUpdate()
                ->first();

            if (! $sequence) {
                $sequence = RegistrationSequence::create([
                    'race_category_id' => $category->id,
                    'current_number' => 0,
                ]);
            }

            // Increment sequence
            $sequence->increment('current_number');
            $sequence->refresh();

            // Generate registration number
            $registrationNumber = sprintf(
                '%s-%04d',
                $category->registration_prefix,
                $sequence->current_number
            );

            return $registrationNumber;
        });
    }
}
