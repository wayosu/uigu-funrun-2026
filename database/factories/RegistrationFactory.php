<?php

namespace Database\Factories;

use App\Enums\PaymentStatus;
use App\Enums\RegistrationType;
use App\Models\RaceCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Registration>
 */
class RegistrationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'race_category_id' => RaceCategory::factory(),
            'registration_number' => 'REG-'.fake()->unique()->numberBetween(10000, 99999),
            'registration_type' => RegistrationType::Individual,
            'participants_count' => 1,
            'pic_name' => fake()->name(),
            'pic_email' => fake()->unique()->safeEmail(),
            'pic_phone' => '08'.fake()->numerify('##########'),
            'total_amount' => fake()->numberBetween(100000, 500000),
            'status' => 'pending_payment',
            'expired_at' => now()->addHours(48),
        ];
    }
}
