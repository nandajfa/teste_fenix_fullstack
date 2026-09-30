<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class ExamHasAttemptsException extends Exception
{
    public function __construct()
    {
        parent::__construct('Esta prova já foi respondida e  suas questões não podem ser alteradas');
    }

    public function render(): JsonResponse
    {
        return response()->json(['message' => $this->getMessage()], 409);
    }
}
