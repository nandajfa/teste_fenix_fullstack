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
     * Display a listing of the resource.
     */
    public function index(): AnonymousResourceCollection
    {
        return ExamResource::collection($this->examService->paginate());
    }

    public function store(StoreExamRequest $request): ExamResource
    {
        return new ExamResource($this->examService->create($request->validated()));
    }

    public function show(Exam $exam): ExamResource
    {
        return new ExamResource($this->examService->loadDetails($exam));
    }

    public function update(UpdateExamRequest $request, Exam $exam): ExamResource
    {
        return new ExamResource($this->examService->update($exam, $request->validated()));
    }

    public function destroy(Exam $exam): Response
    {
        $this->examService->delete($exam);

        return response()->noContent();
    }
}
