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

            return compact('overall', 'best', 'worst', 'exams', 'students');
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

        return ['items' => $items, 'total' => $total];
    }
}
