<?php

namespace App\Presentation\User\Middleware;

use Closure;
use Illuminate\Http\Request;

class IsAuthorizedMiddleware
{
    public function handle(Request $request,Closure $next)
    {
        if (!auth()->check()) {
            // Для веб-запросов - редирект
            return redirect()->route('authorization-form')
                ->with('message', 'You need to login first');
        }

        return $next($request);
    }
}
