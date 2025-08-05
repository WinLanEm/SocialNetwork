<?php

namespace App\Presentation\User\Middleware;

use Closure;
use Illuminate\Http\Request;

class IsNotAuthorizedMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check()) {
            // Для веб-запросов
            return redirect()->route('home')->with('message', 'You need to logout first');
        }

        return $next($request);
    }
}
