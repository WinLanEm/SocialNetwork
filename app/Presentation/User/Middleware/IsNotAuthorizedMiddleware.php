<?php

namespace App\Presentation\User\Middleware;

use Closure;
use Illuminate\Http\Request;

class IsNotAuthorizedMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if(auth()->user()){
            return redirect()->route('home')->with('message','Доступ к странице есть только у не авторизованных пользователей');
        };
        return $next($request);
    }
}
