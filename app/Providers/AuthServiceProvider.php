<?php

namespace App\Providers;

use App\Exceptions\JsonAuthorizationException;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Gate::define('view-chat', function ($user, $chat) {
            if (!in_array($user->id, $chat->participants)) {
                throw new JsonAuthorizationException('You do not have permission to see this chat');
            }
            return true;
        });
    }
}
