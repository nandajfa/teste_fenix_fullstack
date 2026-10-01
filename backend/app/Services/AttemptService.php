<?php

namespace App\Services;

use App\Exceptions\AttemptAlreadyExistsException;
use App\Models\Attempt;
use App\Models\Exam;
use App\Models\Student;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

class AttemptService
{
    public function __construct(private readonly GradingService $grading) {}

    /**
     * @param  array<int, array{question_id: int, alternative_id?: int|null}>  $answers
     */
    public function submit(Student $student, Exam $exam, array $answers): Attempt
    {
        if ($exam->attempts()->where('student_id', $student->id)->exists()) {
            throw new AttemptAlreadyExistsException;
        }

        $chosen = collect($answers)->mapWithKeys(fn (array $answer) => [
            (int) $answer['question_id'] => isset($answer['alternative_id']) ? (int) $answer['alternative_id'] : null,
        ])->all();

        $result = $this->grading->grade($exam->questions()->with('alternatives')->get(), $chosen);

        try {
            return DB::transaction(function () use ($student, $exam, $result) {
                $attempt = Attempt::create([
                    'student_id' => $student->id,
                    'exam_id' => $exam->id,
                    'correct_count' => $result->correctCount,
                    'total_questions' => $result->totalQuestions,
                    'score' => $result->score,
                    'percentage' => $result->percentage,
                    'submitted_at' => now(),
                ]);

                $attempt->answers()->createMany($result->answers);

                return $this->loadResult($attempt);
            });
        } catch (UniqueConstraintViolationException) {
            throw new AttemptAlreadyExistsException;
        }
    }

    public function loadResult(Attempt $attempt): Attempt
    {
        return $attempt->load([
            'student',
            'exam',
            'answers.question.alternatives',
            'answers.alternative',
        ]);
    }
}
