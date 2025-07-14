<?php

namespace App\Presentation\User\Middleware;

use Closure;
use Illuminate\Http\Request;

class IsAuthorizedMiddleware
{
    public function handle(Request $request,Closure $next)
    {
        if(!auth()->user()){
            return redirect()->route('authorization-form')->with('message','Для доступа к этой странице нужно авторизоваться');
        }
        return $next($request);
    }
}
