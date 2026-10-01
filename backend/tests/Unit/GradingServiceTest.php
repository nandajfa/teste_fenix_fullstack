<?php

namespace Tests\Unit;

use App\Models\Alternative;
use App\Models\Question;
use App\Services\GradingService;
use Tests\TestCase;

class GradingServiceTest extends TestCase
{
    private GradingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new GradingService;
    }

    /**
     * Monta uma questão em memória, sem banco de dados.
     */
    private function question(int $id, float $points, int $correctId, array $alternativeIds): Question
    {
        $question = new Question(['points' => $points]);
        $question->id = $id;

        $question->setRelation('alternatives', collect($alternativeIds)->map(function (int $altId) use ($correctId) {
            $alternative = new Alternative(['is_correct' => $altId === $correctId]);
            $alternative->id = $altId;

            return $alternative;
        }));

        return $question;
    }

    public function test_all_correct_answers_give_full_score(): void
    {
        $questions = collect([
            $this->question(1, 2, correctId: 10, alternativeIds: [10, 11]),
            $this->question(2, 3, correctId: 21, alternativeIds: [20, 21]),
        ]);

        $result = $this->service->grade($questions, [1 => 10, 2 => 21]);

        $this->assertSame(2, $result->correctCount);
        $this->assertSame(2, $result->totalQuestions);
        $this->assertSame(5.0, $result->score);
        $this->assertSame(100.0, $result->percentage);
    }

    public function test_wrong_answers_score_zero(): void
    {
        $questions = collect([$this->question(1, 1, correctId: 10, alternativeIds: [10, 11])]);

        $result = $this->service->grade($questions, [1 => 11]);

        $this->assertSame(0, $result->correctCount);
        $this->assertSame(0.0, $result->score);
        $this->assertSame(0.0, $result->percentage);
        $this->assertFalse($result->answers[0]['is_correct']);
    }

    public function test_blank_answers_are_recorded_as_wrong(): void
    {
        $questions = collect([$this->question(1, 1, correctId: 10, alternativeIds: [10, 11])]);

        $result = $this->service->grade($questions, []);

        $this->assertSame(0, $result->correctCount);
        $this->assertNull($result->answers[0]['alternative_id']);
        $this->assertFalse($result->answers[0]['is_correct']);
    }

    public function test_score_uses_question_weights_and_percentage_uses_count(): void
    {
        $questions = collect([
            $this->question(1, 3, correctId: 10, alternativeIds: [10, 11]),
            $this->question(2, 1, correctId: 20, alternativeIds: [20, 21]),
        ]);

        $result = $this->service->grade($questions, [1 => 10, 2 => 21]);

        $this->assertSame(3.0, $result->score);
        $this->assertSame(50.0, $result->percentage);
    }

    public function test_percentage_is_rounded_to_two_decimals(): void
    {
        $questions = collect([
            $this->question(1, 1, correctId: 10, alternativeIds: [10, 11]),
            $this->question(2, 1, correctId: 20, alternativeIds: [20, 21]),
            $this->question(3, 1, correctId: 30, alternativeIds: [30, 31]),
        ]);

        $result = $this->service->grade($questions, [1 => 10]);

        $this->assertSame(33.33, $result->percentage);
    }

    public function test_decimal_points_are_summed_without_float_errors(): void
    {
        $questions = collect([
            $this->question(1, 0.1, correctId: 10, alternativeIds: [10, 11]),
            $this->question(2, 0.2, correctId: 20, alternativeIds: [20, 21]),
        ]);

        $result = $this->service->grade($questions, [1 => 10, 2 => 20]);

        $this->assertSame(0.3, $result->score);
    }
}
