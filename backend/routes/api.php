<?php

use App\Http\Controllers\Api\ExamController;
use Illuminate\Support\Facades\Route;

// Rotas da API. O Laravel adiciona o prefixo /api automaticamente.

Route::get('/health', fn () => ['status' => 'ok']);
Route::apiResource('exams', ExamController::class);
