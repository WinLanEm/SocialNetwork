<?php

namespace App\Presentation\User\Controllers;

use App\Common\Controllers\Controller;
use App\Presentation\User\Requests\AuthorizationRequest;
use Illuminate\Support\Facades\Auth;

class ApiAuthorizationController extends Controller
{
    public function __invoke(AuthorizationRequest $request)
    {
        if(!Auth::attempt($request->only(['phone', 'password']))) {
            return response()->json([
                'status' => false,
                'message' => 'Bad Request',
            ],422);
        }
        return [
            'token' => $request->user()->createToken('api-token')->plainTextToken,
            'user' => $request->user()
        ];
    }
}
