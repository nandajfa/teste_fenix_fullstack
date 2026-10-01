<?php

namespace App\Services;

final readonly class GradingResult
{
    /**
     * @param  array<int, array{question_id: int, alternative_id: int|null, is_correct: bool}>  $answers
     */
    public function __construct(
        public int $correctCount,
        public int $totalQuestions,
        public float $score,
        public float $percentage,
        public array $answers,
    ) {}
}
