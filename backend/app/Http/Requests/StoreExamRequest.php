<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreExamRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'questions' => ['required', 'array', 'min:1'],
            'questions.*.statement' => ['required', 'string'],
            'questions.*.points' => ['nullable', 'numeric', 'min:0.01', 'max:999.99'],
            'questions.*.alternatives' => ['required', 'array', 'min:2', 'max:6'],
            'questions.*.alternatives.*.text' => ['required', 'string'],
            'questions.*.alternatives.*.is_correct' => ['required', 'boolean'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                foreach ($this->input('questions', []) as $index => $question) {
                    $correctCount = collect($question['alternatives'])
                        ->filter(fn ($alt) => filter_var($alt['is_correct'], FILTER_VALIDATE_BOOLEAN))
                        ->count();

                    if ($correctCount !== 1) {
                        $validator->errors()->add(
                            "questions.{$index}.alternatives",
                            'Cada questão deve ter exatamente uma alternativa correta'
                        );
                    }
                }
            },
        ];
    }
}
