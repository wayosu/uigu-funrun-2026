<?php

namespace Database\Factories;

use App\Enums\Gender;
use App\Models\Registration;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Participant>
 */
class ParticipantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'registration_id' => Registration::factory(),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => '08'.fake()->numerify('##########'),
            'gender' => fake()->randomElement(Gender::cases()),
            'birth_date' => fake()->date('Y-m-d', '-18 years'),
            'jersey_size' => \App\Models\JerseySize::query()->active()->inRandomOrder()->first()?->code ?? 'm',
            'identity_number' => fake()->numerify('################'),
            'blood_type' => fake()->randomElement(['A', 'B', 'AB', 'O']),
            'emergency_name' => fake()->name(),
            'emergency_phone' => '08'.fake()->numerify('##########'),
            'emergency_relation' => fake()->randomElement(['Parent', 'Spouse', 'Sibling', 'Friend']),
            'is_pic' => false,
        ];
    }

    public function pic(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_pic' => true,
        ]);
    }
}
