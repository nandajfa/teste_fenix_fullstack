<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RankingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
public function toArray(Request $request): array
{
    return [
        'position' => (int) $this->position,
        'attempt_id' => $this->id,
        'student' => ['id' => $this->student->id, 'name' => $this->student->name],
        'exam' => ['id' => $this->exam->id, 'title' => $this->exam->title],
        'score' => (float) $this->score,
        'percentage' => (float) $this->percentage,
        'correct_count' => $this->correct_count,
        'total_questions' => $this->total_questions,
        'submitted_at' => $this->submitted_at,
    ];
}
}
