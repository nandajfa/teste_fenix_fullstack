<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentExamResource extends JsonResource
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
            'title' => $this->title,
            'description' => $this->description,
            'questions' => $this->questions->map(fn ($question) => [
                'id' => $question->id,
                'statement' => $question->statement,
                'points' => (float) $question->points,
                'position' => $question->position,
                'alternatives' => $question->alternatives->map(fn ($alternative) => [
                    'id' => $alternative->id,
                    'text' => $alternative->text,
                    'position' => $alternative->position,
                ]),
            ]),
        ];
    }
}
