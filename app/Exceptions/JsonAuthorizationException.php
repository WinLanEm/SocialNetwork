<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;

class JsonAuthorizationException extends AuthorizationException
{
    protected $statusCode = 403;

    public function render($request): JsonResponse
    {
        return response()->json([
            'status' => false,
            'message' => $this->getMessage() ?: 'Forbidden',
        ], $this->statusCode);
    }
}
