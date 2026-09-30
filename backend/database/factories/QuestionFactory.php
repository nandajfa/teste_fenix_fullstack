<?php

namespace Database\Factories;

use App\Models\Exam;
use App\Models\Question;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Question>
 */
class QuestionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'exam_id' => Exam::factory(),
            'statement' => rtrim(fake()->sentence(), '.').'?',
            'points' => 1,
            'position' => 1,
        ];
    }

    public function withAlternatives(int $count = 4): static
    {
        return $this->afterCreating(function (Question $question) use ($count) {
            $correct = fake()->numberBetween(1, $count);

            foreach (range(1, $count) as $position) {
                $question->alternatives()->create([
                    'text' => fake()->words(3, true),
                    'position' => $position,
                    'is_correct' => $position === $correct,
                ]);
            }
        });
    }
}
