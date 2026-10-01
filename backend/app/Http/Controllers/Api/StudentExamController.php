<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\StudentExamResource;
use App\Models\Exam;
use App\Models\Student;
use App\Services\ExamService;

class StudentExamController extends Controller
{
    public function __construct(private readonly ExamService $examService) {}

    public function show(Student $student, Exam $exam): StudentExamResource
    {
        return new StudentExamResource($this->examService->loadDetails($exam));
    }
}
