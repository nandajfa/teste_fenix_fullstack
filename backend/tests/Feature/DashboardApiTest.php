<?php

namespace Tests\Feature;

use App\Models\Attempt;
use App\Models\Exam;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardApiTest extends TestCase
{
    use RefreshDatabase;

    private function attempt(Exam $exam, float $percentage, float $score = 1): Attempt
    {
        return Attempt::factory()->for($exam)->create([
            'percentage' => $percentage,
            'score' => $score,
        ]);
    }

    public function test_summary_shows_average_best_and_worst(): void
    {
        $exam = Exam::factory()->create();
        $best = $this->attempt($exam, 100);
        $this->attempt($exam, 50);
        $worst = $this->attempt($exam, 0);

        $this->getJson('/api/dashboard')
            ->assertOk()
            ->assertJsonPath('data.overall.attempts_count', 3)
            ->assertJsonPath('data.overall.average_percentage', 50)
            ->assertJsonPath('data.best_attempt.id', $best->id)
            ->assertJsonPath('data.worst_attempt.id', $worst->id)
            ->assertJsonPath('data.exams.0.attempts_count', 3);
    }

    public function test_summary_ignores_deleted_exams(): void
    {
        $this->attempt(Exam::factory()->create(), 80);
        $deleted = Exam::factory()->create();
        $this->attempt($deleted, 10);
        $deleted->delete();

        $this->getJson('/api/dashboard')
            ->assertOk()
            ->assertJsonPath('data.overall.attempts_count', 1)
            ->assertJsonPath('data.overall.worst_percentage', 80)
            ->assertJsonCount(1, 'data.exams');
    }

    public function test_summary_without_attempts_returns_empty_metrics(): void
    {
        $this->getJson('/api/dashboard')
            ->assertOk()
            ->assertJsonPath('data.overall.attempts_count', 0)
            ->assertJsonPath('data.overall.average_percentage', null)
            ->assertJsonPath('data.best_attempt', null);
    }

    public function test_ranking_orders_by_percentage_and_ties_share_position(): void
    {
        $exam = Exam::factory()->create();
        $this->attempt($exam, 50);
        $this->attempt($exam, 100);
        $this->attempt($exam, 80, score: 8);
        $this->attempt($exam, 80, score: 8);

        $this->getJson('/api/dashboard/ranking')
            ->assertOk()
            ->assertJsonPath('data.0.percentage', 100)
            ->assertJsonPath('data.0.position', 1)
            ->assertJsonPath('data.1.position', 2)
            ->assertJsonPath('data.2.position', 2)
            ->assertJsonPath('data.3.position', 4);
    }

    public function test_ranking_is_paginated_with_global_positions(): void
    {
        $exam = Exam::factory()->create();
        foreach ([100, 90, 80, 70, 60] as $percentage) {
            $this->attempt($exam, $percentage);
        }

        $this->getJson('/api/dashboard/ranking?per_page=2&page=2')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.position', 3)
            ->assertJsonPath('meta.total', 5);
    }

    public function test_ranking_filters_by_exam(): void
    {
        $exam = Exam::factory()->create();
        $this->attempt($exam, 70);
        $this->attempt(Exam::factory()->create(), 90);

        $this->getJson("/api/dashboard/ranking?exam_id={$exam->id}")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.position', 1);
    }

    public function test_ranking_validates_parameters(): void
    {
        $this->getJson('/api/dashboard/ranking?per_page=500')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['per_page']);
    }

    public function test_summary_is_cached_and_invalidated_by_new_attempt(): void
    {
        $exam = Exam::factory()->withQuestions(1)->create();
        $this->attempt($exam, 100);

        $this->getJson('/api/dashboard')->assertJsonPath('data.overall.attempts_count', 1);


        $this->attempt($exam, 50);
        $this->getJson('/api/dashboard')->assertJsonPath('data.overall.attempts_count', 1);

        $student = Student::factory()->create();
        $this->postJson("/api/students/{$student->id}/exams/{$exam->id}/attempts", ['answers' => []])
            ->assertCreated();

        $this->getJson('/api/dashboard')->assertJsonPath('data.overall.attempts_count', 3);
    }
}
