<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\StudentExamResource;
use App\Http\Resources\StudentExamSummaryResource;
use App\Models\Exam;
use App\Models\Student;
use App\Services\ExamService;
use App\Services\StudentService;

class StudentExamController extends Controller
{
    public function __construct(
        private readonly ExamService $examService,
        private readonly StudentService $studentService,
    ) {}

    public function show(Student $student, Exam $exam): StudentExamResource
    {
        return new StudentExamResource($this->examService->loadDetails($exam));
    }

    public function index(Student $student)
    {
        return StudentExamSummaryResource::collection($this->studentService->examsWithStatus($student));
    }
}
