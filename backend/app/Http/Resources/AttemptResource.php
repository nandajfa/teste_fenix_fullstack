<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttemptResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'student' => $this->whenLoaded('student', fn () => [
                'id' => $this->student->id,
                'name' => $this->student->name,
            ]),
            'exam' => $this->whenLoaded('exam', fn () => [
                'id' => $this->exam->id,
                'title' => $this->exam->title,
                'is_deleted' => $this->exam->trashed(),
            ]),
            'correct_count' => $this->correct_count,
            'total_questions' => $this->total_questions,
            'score' => (float) $this->score,
            'percentage' => (float) $this->percentage,
            'submitted_at' => $this->submitted_at,
            'answers' => AnswerResource::collection($this->whenLoaded('answers')),
        ];
    }
}
