<?php

use App\Http\Controllers\Api\AttemptController;
use App\Http\Controllers\Api\ExamController;
use App\Http\Controllers\Api\StudentExamController;
use Illuminate\Support\Facades\Route;

// Rotas da API. O Laravel adiciona o prefixo /api automaticamente.

Route::get('/health', fn () => ['status' => 'ok']);
Route::apiResource('exams', ExamController::class);
Route::get('students/{student}/exams/{exam}', [StudentExamController::class, 'show']);
Route::post('students/{student}/exams/{exam}/attempts', [AttemptController::class, 'store']);
Route::get('attempts/{attempt}', [AttemptController::class, 'show']);
