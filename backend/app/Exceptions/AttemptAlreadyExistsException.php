<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class AttemptAlreadyExistsException extends Exception
{
    public function __construct()
    {
        parent::__construct('Este aluno já realizou esta prova.');
    }

    public function render(): JsonResponse
    {
        return response()->Json(['message' => $this->getMessage()], 409);
    }
}
