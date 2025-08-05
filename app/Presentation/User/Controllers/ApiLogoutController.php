<?php

namespace App\Presentation\User\Controllers;

use App\Common\Controllers\Controller;
use Illuminate\Http\Client\Request;

class ApiLogoutController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->noContent();
    }
}
