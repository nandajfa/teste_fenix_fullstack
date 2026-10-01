<?php

namespace Tests\Feature;

use App\Models\Exam;
use App\Models\Question;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttemptApiTest extends TestCase
{
    use RefreshDatabase;

    private Student $student;

    private Exam $exam;

    protected function setUp(): void
    {
        parent::setUp();
        $this->student = Student::factory()->create();
        $this->exam = Exam::factory()->withQuestions(2)->create();
    }

    private function questions()
    {
        return $this->exam->questions()->with('alternatives')->get();
    }

    private function correctId(Question $question): int
    {
        return $question->alternatives->firstWhere('is_correct', true)->id;
    }

    private function wrongId(Question $question): int
    {
        return $question->alternatives->firstWhere('is_correct', false)->id;
    }

    private function submitUrl(): string
    {
        return "/api/students/{$this->student->id}/exams/{$this->exam->id}/attempts";
    }

    public function test_student_sees_exam_without_correct_answers(): void
    {
        $this->getJson("/api/students/{$this->student->id}/exams/{$this->exam->id}")
            ->assertOk()
            ->assertJsonCount(2, 'data.questions')
            ->assertJsonMissingPath('data.questions.0.alternatives.0.is_correct');
    }

    public function test_submits_and_grades_attempt(): void
    {
        [$q1, $q2] = $this->questions()->all();

        $this->postJson($this->submitUrl(), ['answers' => [
            ['question_id' => $q1->id, 'alternative_id' => $this->correctId($q1)],
            ['question_id' => $q2->id, 'alternative_id' => $this->wrongId($q2)],
        ]])
            ->assertCreated()
            ->assertJsonPath('data.correct_count', 1)
            ->assertJsonPath('data.total_questions', 2)
            ->assertJsonPath('data.percentage', 50)
            ->assertJsonCount(2, 'data.answers');

        $this->assertDatabaseHas('attempts', ['student_id' => $this->student->id, 'exam_id' => $this->exam->id]);
        $this->assertDatabaseCount('answers', 2);
    }

    public function test_empty_submission_scores_zero(): void
    {
        $this->postJson($this->submitUrl(), ['answers' => []])
            ->assertCreated()
            ->assertJsonPath('data.correct_count', 0)
            ->assertJsonPath('data.percentage', 0);
    }

    public function test_student_cannot_submit_same_exam_twice(): void
    {
        $this->postJson($this->submitUrl(), ['answers' => []])->assertCreated();

        $this->postJson($this->submitUrl(), ['answers' => []])
            ->assertStatus(409)
            ->assertJsonPath('message', 'Este aluno já realizou esta prova.');

        $this->assertDatabaseCount('attempts', 1);
    }

    public function test_rejects_question_from_another_exam(): void
    {
        $otherQuestion = Exam::factory()->withQuestions(1)->create()->questions()->first();

        $this->postJson($this->submitUrl(), ['answers' => [
            ['question_id' => $otherQuestion->id, 'alternative_id' => null],
        ]])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['answers.0.question_id']);
    }

    public function test_rejects_alternative_from_another_question(): void
    {
        [$q1, $q2] = $this->questions()->all();

        $this->postJson($this->submitUrl(), ['answers' => [
            ['question_id' => $q1->id, 'alternative_id' => $this->correctId($q2)],
        ]])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['answers.0.alternative_id']);
    }

    public function test_requires_answers_field(): void
    {
        $this->postJson($this->submitUrl(), [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['answers']);
    }

    public function test_shows_attempt_result_with_correct_answers(): void
    {
        [$q1] = $this->questions()->all();

        $attemptId = $this->postJson($this->submitUrl(), ['answers' => [
            ['question_id' => $q1->id, 'alternative_id' => $this->wrongId($q1)],
        ]])->json('data.id');

        $this->getJson("/api/attempts/{$attemptId}")
            ->assertOk()
            ->assertJsonPath('data.student.id', $this->student->id)
            ->assertJsonPath('data.answers.0.is_correct', false)
            ->assertJsonPath('data.answers.0.correct_alternative.id', $this->correctId($q1));
    }

    public function test_cannot_submit_deleted_exam(): void
    {
        $this->exam->delete();

        $this->postJson($this->submitUrl(), ['answers' => []])->assertNotFound();
    }
}
