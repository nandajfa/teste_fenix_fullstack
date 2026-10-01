<?php

namespace App\Services;

use App\Models\Question;
use Illuminate\Support\Collection;

class GradingService
{
    /**
     * @param  Collection<int, Question>  $questions  questões da prova, com as alternativas carregadas
     * @param  array<int, int|null>  $chosen  [question_id => alternative_id escolhida, ou null]
     */
    public function grade(Collection $questions, array $chosen): GradingResult
    {
        $correctCount = 0;
        $scoreInCents = 0;
        $answers = [];

        foreach ($questions as $question) {
            $alternativeId = $chosen[$question->id] ?? null;
            $correctId = $question->alternatives->firstWhere('is_correct', true)?->id;
            $isCorrect = $alternativeId !== null && $alternativeId === $correctId;

            if ($isCorrect) {
                $correctCount++;
                $scoreInCents += (int) round((float) $question->points * 100);
            }

            $answers[] = [
                'question_id' => $question->id,
                'alternative_id' => $alternativeId,
                'is_correct' => $isCorrect,
            ];
        }

        $total = $questions->count();

        return new GradingResult(
            correctCount: $correctCount,
            totalQuestions: $total,
            score: $scoreInCents / 100,
            percentage: $total > 0 ? round($correctCount / $total * 100, 2) : 0.0,
            answers: $answers,
        );
    }
}
