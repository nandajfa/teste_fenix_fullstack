<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubmitAttemptRequest;
use App\Http\Resources\AttemptResource;
use App\Models\Attempt;
use App\Models\Exam;
use App\Models\Student;
use App\Services\AttemptService;

class AttemptController extends Controller
{
    public function __construct(private readonly AttemptService $attemptService) {}

    public function store(SubmitAttemptRequest $request, Student $student, Exam $exam): AttemptResource
    {
        return new AttemptResource(
            $this->attemptService->submit($student, $exam, $request->validated('answers'))
        );
    }

    public function show(Attempt $attempt): AttemptResource
    {
        return new AttemptResource($this->attemptService->loadResult($attempt));
    }
}
