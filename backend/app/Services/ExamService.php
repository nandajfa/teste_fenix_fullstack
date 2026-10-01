<?php

namespace App\Services;

use App\Exceptions\ExamHasAttemptsException;
use App\Models\Exam;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class ExamService
{
    public function __construct(
        private readonly DashboardService $dashboard,
    ) {}

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Exam::query()
            ->withCount('questions')
            ->latest()
            ->paginate($perPage);
    }

    public function loadDetails(Exam $exam): Exam
    {
        return $exam->load('questions.alternatives');
    }

    public function create(array $data): Exam
    {
        $exam = DB::transaction(function () use ($data) {
            $exam = Exam::create(Arr::only($data, ['title', 'description']));
            $this->createQuestions($exam, $data['questions']);

            return $exam;
        });

        $this->dashboard->invalidate();

        return $this->loadDetails($exam);
    }

    public function update(Exam $exam, array $data): Exam
    {
        $changesQuestions = array_key_exists('questions', $data);

        if ($changesQuestions && $exam->attempts()->exists()) {
            throw new ExamHasAttemptsException;
        }

        DB::transaction(function () use ($exam, $data, $changesQuestions) {
            $exam->update(Arr::only($data, ['title', 'description']));

            if ($changesQuestions) {
                $exam->questions()->delete();
                $this->createQuestions($exam, $data['questions']);
            }
        });

        $this->dashboard->invalidate();

        return $this->loadDetails($exam->refresh());
    }

    public function delete(Exam $exam): void
    {
        $exam->delete();
        $this->dashboard->invalidate();
    }

    private function createQuestions(Exam $exam, array $questions): void
    {
        foreach (array_values($questions) as $qIndex => $questionData) {
            $question = $exam->questions()->create([
                'statement' => $questionData['statement'],
                'points' => $questionData['points'] ?? 1,
                'position' => $qIndex + 1,
            ]);

            $alternatives = collect($questionData['alternatives'])
                ->values()
                ->map(fn ($alt, $aIndex) => [
                    'text' => $alt['text'],
                    'is_correct' => $alt['is_correct'],
                    'position' => $aIndex + 1,
                ]);

            $question->alternatives()->createMany($alternatives);
        }
    }
}
