<?php

namespace App\Services;

use App\Models\Attempt;
use App\Models\Exam;
use App\Models\Student;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Cache;

class DashboardService
{
    private const TTL_SECONDS = 600;

    private const VERSION_KEY = 'dashboard:version';

    public function summary(): array
    {
        return Cache::remember($this->key('summary'), self::TTL_SECONDS, fn () => $this->buildSummary());
    }

    public function ranking(?int $examId, int $page = 1, int $perPage = 10): LengthAwarePaginator
    {
        $key = $this->key('ranking:'.($examId ?? 'all').":{$perPage}:{$page}");

        $data = Cache::remember($key, self::TTL_SECONDS, fn () => $this->buildingRanking($examId, $page, $perPage));

        return new LengthAwarePaginator($data['items'], $data['total'], $perPage, $page, [
            'path' => Paginator::resolveCurrentPath(),
            'query' => array_filter(['exam_id' => $examId, 'per_page' => $perPage]),
        ]);
    }

    public function invalidate(): void
    {
        Cache::increment(self::VERSION_KEY);
    }

    private function key(string $suffix): string
    {
        return 'dashboard:v'.Cache::get(self::VERSION_KEY, 0).':'.$suffix;
    }

    private function activeAttempts(): Builder
    {
        return Attempt::query()->whereIn('exam_id', Exam::query()->select('id'));
    }

    private function buildSummary(): array
    {
        $overall = $this->activeAttempts()
            ->selectRaw('COUNT(*) AS attempts_count')
            ->selectRaw('AVG(percentage) AS average_percentage')
            ->selectRaw('MAX(percentage) AS best_percentage')
            ->selectRaw('MIN(percentage) AS worst_percentage')
            ->toBase()
            ->first();

        $best = $this->activeAttempts()->with(['student', 'exam'])
            ->orderByDesc('percentage')->orderBy('score')->orderBy('submitted_at')
            ->first();

        $worst = $this->activeAttempts()->with(['student', 'exam'])
            ->orderBy('percentage')->orderBy('score')->orderBy('submitted_at')
            ->first();

        $exams = Exam::query()
            ->withCount('attempts')
            ->withAvg('attempts', 'percentage')
            ->withMax('attempts', 'percentage')
            ->withMin('attempts', 'percentage')
            ->orderBy('title')
            ->get();

        $onlyActive = fn ($query) => $query->whereIn('exam_id', Exam::query()->select('id'));

        $students = Student::query()
            ->withCount(['attempts' => $onlyActive])
            ->withAvg(['attempts' => $onlyActive], 'percentage')
            ->orderBy('name')
            ->get();

        return [
            'overall' => [
                'attempts_count' => (int) $overall->attempts_count,
                'average_percentage' => $this->round($overall->average_percentage),
                'best_percentage' => $this->round($overall->best_percentage),
                'worst_percentage' => $this->round($overall->worst_percentage),
            ],
            'best_attempt' => $best ? $this->attemptData($best) : null,
            'worst_attempt' => $worst ? $this->attemptData($worst) : null,
            'exams' => $exams->map(fn (Exam $exam) => [
                'id' => $exam->id,
                'title' => $exam->title,
                'attempts_count' => $exam->attempts_count,
                'average_percentage' => $this->round($exam->attempts_avg_percentage),
                'best_percentage' => $this->round($exam->attempts_max_percentage),
                'worst_percentage' => $this->round($exam->attempts_min_percentage),
            ])->all(),
            'students' => $students->map(fn (Student $student) => [
                'id' => $student->id,
                'name' => $student->name,
                'attempts_count' => $student->attempts_count,
                'average_percentage' => $this->round($student->attempts_avg_percentage),
            ])->all(),
        ];
    }

    private function buildingRanking(?int $examId, int $page, int $perPage): array
    {
        $query = $this->activeAttempts()
            ->when($examId, fn ($q) => $q->where('exam_id', $examId));

        $total = (clone $query)->count();

        $items = $query
            ->select('attempts.*')
            ->selectRaw('RANK() OVER (ORDER BY percentage DESC, score DESC) AS position')
            ->with(['student', 'exam'])
            ->orderBy('position')
            ->orderBy('attempts.id')
            ->forPage($page, $perPage)
            ->get();

        return [
            'items' => $items->map(fn (Attempt $attempt) => [
                'position' => (int) $attempt->position,
                'attempt_id' => $attempt->id,
                'student' => ['id' => $attempt->student->id, 'name' => $attempt->student->name],
                'exam' => ['id' => $attempt->exam->id, 'title' => $attempt->exam->title],
                'score' => (float) $attempt->score,
                'percentage' => (float) $attempt->percentage,
                'correct_count' => $attempt->correct_count,
                'total_questions' => $attempt->total_questions,
                'submitted_at' => $attempt->submitted_at?->toIso8601String(),
            ])->all(),
            'total' => $total,
        ];
    }

    private function attemptData(Attempt $attempt): array
    {
        return [
            'id' => $attempt->id,
            'student' => ['id' => $attempt->student->id, 'name' => $attempt->student->name],
            'exam' => ['id' => $attempt->exam->id, 'title' => $attempt->exam->title],
            'score' => (float) $attempt->score,
            'percentage' => (float) $attempt->percentage,
        ];
    }

    private function round(mixed $value): ?float
    {
        return $value === null ? null : round((float) $value, 2);
    }
}
