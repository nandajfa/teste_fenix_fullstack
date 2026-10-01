<?php

namespace App\Services;

use App\Models\Exam;
use App\Models\Student;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class StudentService
{
    public function list(): Collection
    {
        return Student::query()->orderBy('name')->get();
    }

    public function examsWithStatus(Student $student): Collection
    {
        return Exam::query()
            ->withCount('questions')
            ->with(['attempts' => fn ($query) => $query->where('student_id', $student->id)])
            ->orderBy('title')
            ->get();
    }

    public function attemptHistory(Student $student): LengthAwarePaginator
    {
        return $student->attempts()
            ->with('exam')
            ->latest('submitted_at')
            ->paginate();
    }
}
