<?php

namespace App\Presentation\User\Middleware;

use Closure;
use Illuminate\Http\Request;


class RefreshCSRFTokenMiddleware
{
    public function handle(Request $request,Closure $next)
    {
        $response = $next($request);

        if ($request->ajax() || $request->wantsJson()) {
            $response->headers->set('X-CSRF-TOKEN', csrf_token());
        }

        return $response;
    }
}
