<?php

namespace Database\Factories;

use App\Models\Attempt;
use App\Models\Exam;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Attempt>
 */
class AttemptFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $total = 10;
        $correct = fake()->numberBetween(0, $total);

        return [
            'student_id' => Student::factory(),
            'exam_id' => Exam::factory(),
            'correct_count' => $correct,
            'total_questions' => $total,
            'score' => $correct,
            'percentage' => round($correct / $total * 100, 2),
            'submitted_at' => now(),
        ];
    }
}
