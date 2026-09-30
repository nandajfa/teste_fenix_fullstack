<?php

namespace Tests\Feature;

use App\Models\Attempt;
use App\Models\Exam;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExamApiTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Prova de História',
            'description' => 'Brasil Colônia',
            'questions' => [
                [
                    'statement' => 'Em que ano o Brasil foi descoberto?',
                    'points' => 2,
                    'alternatives' => [
                        ['text' => '1500', 'is_correct' => true],
                        ['text' => '1822', 'is_correct' => false],
                    ],
                ],
            ],
        ], $overrides);
    }

    public function test_lists_exams_paginated(): void
    {
        Exam::factory()->count(3)->create();

        $this->getJson('/api/exams')
            ->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure(['data' => [['id', 'title', 'questions_count']], 'meta', 'links']);
    }

    public function test_creates_exam_with_questions_and_alternatives(): void
    {
        $this->postJson('/api/exams', $this->payload())
            ->assertCreated()
            ->assertJsonPath('data.title', 'Prova de História')
            ->assertJsonPath('data.questions.0.alternatives.0.is_correct', true);

        $this->assertDatabaseHas('questions', ['statement' => 'Em que ano o Brasil foi descoberto?', 'position' => 1]);
        $this->assertDatabaseCount('alternatives', 2);
    }

    public function test_requires_title_and_questions(): void
    {
        $this->postJson('/api/exams', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['title', 'questions']);
    }

    public function test_rejects_question_with_two_correct_alternatives(): void
    {
        $payload = $this->payload();
        $payload['questions'][0]['alternatives'][1]['is_correct'] = true;

        $this->postJson('/api/exams', $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['questions.0.alternatives']);
    }

    public function test_rejects_question_without_correct_alternative(): void
    {
        $payload = $this->payload();
        $payload['questions'][0]['alternatives'][0]['is_correct'] = false;

        $this->postJson('/api/exams', $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['questions.0.alternatives']);
    }

    public function test_shows_exam_with_questions_and_alternatives(): void
    {
        $exam = Exam::factory()->withQuestions(2)->create();

        $this->getJson("/api/exams/{$exam->id}")
            ->assertOk()
            ->assertJsonCount(2, 'data.questions')
            ->assertJsonCount(4, 'data.questions.0.alternatives');
    }

    public function test_returns_404_for_missing_exam(): void
    {
        $this->getJson('/api/exams/999')->assertNotFound();
    }

    public function test_updates_title_and_replaces_questions(): void
    {
        $exam = Exam::factory()->withQuestions(3)->create();

        $this->putJson("/api/exams/{$exam->id}", $this->payload(['title' => 'Novo título']))
            ->assertOk()
            ->assertJsonPath('data.title', 'Novo título')
            ->assertJsonCount(1, 'data.questions');

        $this->assertDatabaseCount('questions', 1);
    }

    public function test_blocks_question_changes_when_exam_has_attempts(): void
    {
        $exam = Exam::factory()->withQuestions(2)->create();
        Attempt::factory()->for($exam)->create();

        $this->putJson("/api/exams/{$exam->id}", $this->payload())
            ->assertStatus(409);

        $this->assertDatabaseCount('questions', 2);
    }

    public function test_allows_title_update_when_exam_has_attempts(): void
    {
        $exam = Exam::factory()->withQuestions(2)->create();
        Attempt::factory()->for($exam)->create();

        $this->putJson("/api/exams/{$exam->id}", ['title' => 'Título corrigido'])
            ->assertOk()
            ->assertJsonPath('data.title', 'Título corrigido');
    }

    public function test_soft_deletes_exam(): void
    {
        $exam = Exam::factory()->create();

        $this->deleteJson("/api/exams/{$exam->id}")->assertNoContent();

        $this->assertSoftDeleted($exam);
        $this->getJson("/api/exams/{$exam->id}")->assertNotFound();
    }
}
