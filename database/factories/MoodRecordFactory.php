<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MoodRecord>
 */
class MoodRecordFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(), // Buat user baru jika tidak disediakan
            'mood' => $this->faker->randomElement(['netral', 'senyum', 'sedih', 'lelah', 'marah']),
            'reason' => $this->faker->sentence,
            'suggestion_action' => $this->faker->sentence,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
