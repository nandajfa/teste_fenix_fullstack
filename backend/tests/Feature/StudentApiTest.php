<?php

namespace Tests\Feature;

use App\Models\Attempt;
use App\Models\Exam;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_lists_students_ordered_by_name(): void
    {
        Student::factory()->create(['name' => 'Bruno']);
        Student::factory()->create(['name' => 'Ana']);

        $this->getJson('/api/students')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.name', 'Ana');
    }

    public function test_lists_exams_with_student_attempt_status(): void
    {
        $student = Student::factory()->create();
        $done = Exam::factory()->create(['title' => 'A - Realizada']);
        $pending = Exam::factory()->create(['title' => 'B - Pendente']);

        Attempt::factory()->for($student)->for($done)->create(['percentage' => 75]);
        Attempt::factory()->for($pending)->create();

        $this->getJson("/api/students/{$student->id}/exams")
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.attempt.percentage', 75)
            ->assertJsonPath('data.1.attempt', null);
    }

    public function test_deleted_exams_are_not_listed_as_available(): void
    {
        $student = Student::factory()->create();
        Exam::factory()->create()->delete();

        $this->getJson("/api/students/{$student->id}/exams")
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_shows_only_own_attempts_including_deleted_exams(): void
    {
        $student = Student::factory()->create();
        $exam = Exam::factory()->create();

        Attempt::factory()->for($student)->for($exam)->create();
        Attempt::factory()->create();

        $exam->delete();

        $this->getJson("/api/students/{$student->id}/attempts")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.exam.is_deleted', true);
    }

    public function test_returns_404_for_unknown_student(): void
    {
        $this->getJson('/api/students/999/exams')->assertNotFound();
    }
}
