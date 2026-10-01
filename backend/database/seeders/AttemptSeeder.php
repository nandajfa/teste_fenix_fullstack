<?php

namespace Database\Seeders;

use App\Models\Attempt;
use App\Models\Exam;
use App\Models\Student;
use App\Services\AttemptService;
use Illuminate\Database\Seeder;

class AttemptSeeder extends Seeder
{
    public function run(AttemptService $attemptService): void
    {
        if (Attempt::exists()) {
            return;
        }

        $exams = Exam::with('questions.alternatives')->get();
        $students = Student::orderBy('id')->get();

        foreach ($students as $studentIndex => $student) {
            foreach ($exams as $examIndex => $exam) {

                if ($studentIndex === 0 && $examIndex === $exams->count() - 1) {
                    continue;
                }

                $answers = $exam->questions->map(fn ($question) => [
                    'question_id' => $question->id,
                    'alternative_id' => $question->alternatives->random()->id,
                ])->all();

                $attemptService->submit($student, $exam, $answers);
            }
        }
    }
}
