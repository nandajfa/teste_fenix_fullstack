<?php

namespace Database\Factories;

use App\Models\Exam;
use App\Models\Question;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\Sequence;

/**
 * @extends Factory<Exam>
 */
class ExamFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'description' => fake()->optional()->paragraph(),
        ];
    }

    public function withQuestions(int $questions = 5, int $alternatives = 4): static
    {
        return $this->has(
            Question::factory()
                ->count($questions)
                ->withAlternatives($alternatives)
                ->sequence(fn (Sequence $sequence) => ['position' => $sequence->index = 1]),
            'questions'
        );
    }
}
