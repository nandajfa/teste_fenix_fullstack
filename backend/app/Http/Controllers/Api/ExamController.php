<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreExamRequest;
use App\Http\Requests\UpdateExamRequest;
use App\Http\Resources\ExamResource;
use App\Models\Exam;
use App\Services\ExamService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ExamController extends Controller
{
    public function __construct(private readonly ExamService $examService) {}

    /**
     * Lista as provas
     *
     * Retorna as provas paginadas, com a quantidade de questões de cada uma.
     */
    public function index(): AnonymousResourceCollection
    {
        return ExamResource::collection($this->examService->paginate());
    }

    /**
     * Cria uma prova
     *
     * Cria a prova com todas as questões e alternativas em uma única operação.
     * Cada questão deve ter exatamente uma alternativa correta.
     */
    public function store(StoreExamRequest $request): ExamResource
    {
        return new ExamResource($this->examService->create($request->validated()));
    }

    /**
     * Detalha uma prova
     *
     * Retorna a prova com as questões e alternativas, incluindo o gabarito.
     */
    public function show(Exam $exam): ExamResource
    {
        return new ExamResource($this->examService->loadDetails($exam));
    }

    /**
     * Atualiza uma prova
     *
     * Se `questions` for enviado, substitui todas as questões.
     * Retorna 409 se a prova já tiver tentativas e houver alteração nas questões.
     */
    public function update(UpdateExamRequest $request, Exam $exam): ExamResource
    {
        return new ExamResource($this->examService->update($exam, $request->validated()));
    }

    /**
     * Exclui uma prova
     *
     * Exclusão lógica (soft delete): o histórico de tentativas é preservado.
     */
    public function destroy(Exam $exam): Response
    {
        $this->examService->delete($exam);

        return response()->noContent();
    }
}
