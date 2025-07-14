<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'is_not_authorized' => \App\Presentation\User\Middleware\IsNotAuthorizedMiddleware::class,
            'is_authorized' => \App\Presentation\User\Middleware\IsAuthorizedMiddleware::class,
            'intertia' => \App\Presentation\Common\Middleware\HandleInertiaRequests::class
        ]);
        $middleware->appendToGroup('web', \App\Presentation\Common\Middleware\HandleInertiaRequests::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
