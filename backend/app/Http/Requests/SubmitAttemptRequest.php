<?php

namespace App\Http\Requests;

use App\Models\Alternative;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class SubmitAttemptRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'answers' => ['present', 'array'],
            'answers.*.question_id' => ['required', 'integer', 'distinct'],
            'answers.*.alternative_id' => ['nullable', 'integer'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->isnotEmpty()) {
                    return;
                }

                $exam = $this->route('exam');

                $questionIds = $exam->questions()->pluck('id');

                $alternativeToQuestion = Alternative::query()
                    ->whereIn('question_id', $questionIds)
                    ->pluck('question_id', 'id');

                foreach ($this->input('answers', []) as $index => $answer) {
                    $questionId = (int) $answer['question_id'];

                    if (! $questionIds->contains($questionId)) {
                        $validator->errors()->add("answers.{$index}.question_id", 'A questão não pertence a esta prova.');

                        continue;
                    }

                    $alternativeId = $answer['alternative_id'] ?? null;

                    if ($alternativeId !== null && ($alternativeToQuestion[(int) $alternativeId] ?? null) !== $questionId) {
                        $validator->errors()->add("answers.{$index}.alternative_id", 'A alternativa não pertence a esta questão.');
                    }
                }
            },
        ];
    }
}
