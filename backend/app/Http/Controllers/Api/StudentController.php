<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\StudentResource;
use App\Services\StudentService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class StudentController extends Controller
{
    public function __construct(private readonly StudentService $studentService) {}

    public function index(): AnonymousResourceCollection
    {
        return StudentResource::collection($this->studentService->list());
    }
}
