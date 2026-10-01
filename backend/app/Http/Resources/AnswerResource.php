<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnswerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $correct = $this->question->alternatives->firstWhere('is_correct', true);

        return [
            'question_id' => $this->question_id,
            'statement' => $this->question->statement,
            'chosen_alternative' => $this->alternative
                ? ['id' => $this->alternative->id, 'text' => $this->alternative->text]
                : null,
            'correct_alternative' => $correct
                ? ['id' => $correct->id, 'text' => $correct->text]
                : null,
            'is_correct' => $this->is_correct,
        ];
    }
}
