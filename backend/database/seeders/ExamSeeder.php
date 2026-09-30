<?php

namespace Database\Seeders;

use App\Models\Exam;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Exam::exists()) {
            return;
        }

        $exams = [
            [
                'title' => 'Geografia do Brasil',
                'description' => 'Conhecimentos gerais sobre o território brasileiro.',
                'questions' => [
                    ['statement' => 'Qual é a capital do Brasil?', 'alternatives' => ['Brasília', 'São Paulo', 'Rio de Janeiro', 'Salvador'], 'correct' => 0],
                    ['statement' => 'Qual é o maior estado brasileiro em área?', 'alternatives' => ['Pará', 'Amazonas', 'Mato Grosso', 'Minas Gerais'], 'correct' => 1],
                    ['statement' => 'Quantos estados tem o Brasil?', 'alternatives' => ['24', '25', '26', '27'], 'correct' => 2],
                    ['statement' => 'Qual região brasileira tem mais estados?', 'alternatives' => ['Norte', 'Sudeste', 'Sul', 'Nordeste'], 'correct' => 3],
                ],
            ],
            [
                'title' => 'Matemática Básica',
                'description' => 'Operações fundamentais e porcentagem.',
                'questions' => [
                    ['statement' => 'Quanto é 7 × 8?', 'alternatives' => ['54', '56', '58', '64'], 'correct' => 1],
                    ['statement' => 'Qual é a raiz quadrada de 144?', 'alternatives' => ['11', '12', '13', '14'], 'correct' => 1],
                    ['statement' => 'Quanto é 15% de 200?', 'alternatives' => ['15', '20', '30', '35'], 'correct' => 2],
                    ['statement' => 'Quanto é 2 elevado a 5?', 'alternatives' => ['10', '16', '25', '32'], 'correct' => 3],
                ],
            ],
        ];

        foreach ($exams as $examData) {
            DB::transaction(function () use ($examData) {
                $exam = Exam::create([
                    'title' => $examData['title'],
                    'description' => $examData['description'],
                ]);

                foreach ($examData['questions'] as $qindex => $questionData) {
                    $question = $exam->questions()->create([
                        'statement' => $questionData['statement'],
                        'position' => $qindex + 1,
                    ]);

                    foreach ($questionData['alternatives'] as $aIndex => $text) {
                        $question->alternatives()->create([
                            'text' => $text,
                            'position' => $aIndex + 1,
                            'is_correct' => $aIndex === $questionData['correct'],
                        ]);
                    }
                }
            });
        }
    }
}
