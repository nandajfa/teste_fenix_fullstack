<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentExamSummaryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $attempt = $this->attempts->first();

        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'questions_count' => $this->questions_count,
            'attempt' => $attempt ? [
                'id' => $attempt->id,
                'score' => (float) $attempt->score,
                'percentage' => (float) $attempt->percentage,
                'submitted_at' => $attempt->submitted_at,
            ] : null,
        ];
    }
}
