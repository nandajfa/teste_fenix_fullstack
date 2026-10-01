<?php

namespace App\Http\Resources;
use App\Models\Attempt;
use App\Models\Exam;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DashboardResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
public function toArray(Request $request): array
{
    $overall = $this->resource['overall'];

    return [
        'overall' => [
            'attempts_count' => (int) $overall->attempts_count,
            'average_percentage' => $this->round($overall->average_percentage),
            'best_percentage' => $this->round($overall->best_percentage),
            'worst_percentage' => $this->round($overall->worst_percentage),
        ],
        'best_attempt' => $this->attempt($this->resource['best']),
        'worst_attempt' => $this->attempt($this->resource['worst']),
        'exams' => $this->resource['exams']->map(fn (Exam $exam) => [
            'id' => $exam->id,
            'title' => $exam->title,
            'attempts_count' => $exam->attempts_count,
            'average_percentage' => $this->round($exam->attempts_avg_percentage),
            'best_percentage' => $this->round($exam->attempts_max_percentage),
            'worst_percentage' => $this->round($exam->attempts_min_percentage),
        ]),
        'students' => $this->resource['students']->map(fn (Student $student) => [
            'id' => $student->id,
            'name' => $student->name,
            'attempts_count' => $student->attempts_count,
            'average_percentage' => $this->round($student->attempts_avg_percentage),
        ]),
    ];
}

private function round(mixed $value): ?float
{
    return $value === null ? null : round((float) $value, 2);
}

private function attempt(?Attempt $attempt): ?array
{
    if ($attempt === null) {
        return null;
    }

    return [
        'id' => $attempt->id,
        'student' => ['id' => $attempt->student->id, 'name' => $attempt->student->name],
        'exam' => ['id' => $attempt->exam->id, 'title' => $attempt->exam->title],
        'score' => (float) $attempt->score,
        'percentage' => (float) $attempt->percentage,
    ];
}
}
