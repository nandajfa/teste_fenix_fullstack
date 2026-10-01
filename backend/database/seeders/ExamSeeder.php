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
        'title' => 'Ciências: Corpo Humano',
        'description' => 'Órgãos e sistemas do corpo humano.',
        'questions' => [
            ['statement' => 'Qual órgão bombeia o sangue para o corpo?', 'alternatives' => ['Pulmão', 'Coração', 'Fígado', 'Rim'], 'correct' => 1],
            ['statement' => 'Qual é o maior órgão do corpo humano?', 'alternatives' => ['Fígado', 'Cérebro', 'Pele', 'Intestino'], 'correct' => 2],
            ['statement' => 'Quantos ossos tem o esqueleto de um adulto?', 'alternatives' => ['186', '206', '212', '230'], 'correct' => 1],
            ['statement' => 'Onde acontece a troca de oxigênio e gás carbônico?', 'alternatives' => ['Estômago', 'Rins', 'Alvéolos pulmonares', 'Coração'], 'correct' => 2, 'points' => 2],
        ],
    ],
    [
        'title' => 'História do Brasil',
        'description' => 'Da colonização à República.',
        'questions' => [
            ['statement' => 'Em que ano foi proclamada a Independência do Brasil?', 'alternatives' => ['1500', '1822', '1889', '1922'], 'correct' => 1],
            ['statement' => 'Qual foi a primeira capital do Brasil?', 'alternatives' => ['Rio de Janeiro', 'São Paulo', 'Salvador', 'Recife'], 'correct' => 2],
            ['statement' => 'Em que ano foi assinada a Lei Áurea?', 'alternatives' => ['1822', '1850', '1888', '1889'], 'correct' => 2],
            ['statement' => 'Quem proclamou a República no Brasil?', 'alternatives' => ['Dom Pedro II', 'Marechal Deodoro da Fonseca', 'Getúlio Vargas', 'Tiradentes'], 'correct' => 1, 'points' => 2],
        ],
    ],
    [
        'title' => 'Língua Portuguesa',
        'description' => 'Classes de palavras e ortografia.',
        'questions' => [
            ['statement' => 'Qual destas palavras é um substantivo?', 'alternatives' => ['correr', 'mesa', 'bonito', 'rapidamente'], 'correct' => 1],
            ['statement' => 'Qual é o plural de "cidadão"?', 'alternatives' => ['cidadões', 'cidadães', 'cidadãos', 'cidadãoes'], 'correct' => 2],
            ['statement' => 'Na frase "O menino correu", qual palavra é o verbo?', 'alternatives' => ['O', 'menino', 'correu', 'Nenhuma'], 'correct' => 2],
            ['statement' => 'Qual palavra está escrita corretamente?', 'alternatives' => ['excessão', 'exceção', 'esceção', 'execção'], 'correct' => 1, 'points' => 2],
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
                        'points' => $questionData['points'] ?? 1,
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
