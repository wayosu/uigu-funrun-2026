<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RaceCategory>
 */
class RaceCategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word,
            'slug' => $this->faker->unique()->slug(),
            'distance' => $this->faker->randomElement(['5K', '10K', '21K', '42K']),
            'quota' => 100,
            'price_individual' => 150000,
            'price_collective_5' => 125000,
            'price_collective_10' => 100000,
            'description' => $this->faker->paragraph(),
            'registration_prefix' => strtoupper($this->faker->lexify('???')),
            'bib_start_number' => 1001,
            'bib_end_number' => 2000,
            'bib_current_number' => 1001,
            'is_active' => true,
            'registration_open_at' => now()->subDays(30),
            'registration_close_at' => now()->addDays(30),
        ];
    }
}
