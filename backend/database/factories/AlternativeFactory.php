<?php

namespace Database\Factories;

use App\Models\Alternative;
use App\Models\Question;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Alternative>
 */
class AlternativeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'question_id' => Question::factory(),
            'text' => fake()->words(3, true),
            'is_correct' => false,
            'position' => 1,
        ];
    }

    public function correct(): static
    {
        return $this->state(['is_correct' => true]);
    }
}
