<?php

namespace App\Providers;

use App\Application\Chat\Actions\ChatTokenCryptoAction;
use App\Application\Chat\Actions\GetOrCreateChatAction;
use App\Application\Message\Actions\ChoiceStoreMessageStrategy;
use App\Application\User\Actions\AuthorizationAction;


use App\Application\User\Actions\LogoutAction;
use App\Application\User\Actions\RegisterAction;
use App\Application\User\Tasks\PreparePhoneTask;
use App\Domain\Chat\Actions\ChatTokenCryptoActionInterface;
use App\Domain\Chat\Actions\GetOrCreateChatActionInterface;
use App\Domain\Chat\Repositories\DestroyChatRepositoryInterface;
use App\Domain\Chat\Repositories\GetChatByIdRepositoryInterface;
use App\Domain\Chat\Repositories\GetOrCreateChatRepositoryInterface;
use App\Domain\Message\Actions\ChoiceStoreMessageStrategyInterface;
use App\Domain\User\Actions\AuthorizationActionInterface;
use App\Domain\User\Actions\LogoutActionInterface;
use App\Domain\User\Actions\RegisterActionInterface;
use App\Domain\User\Repositories\AuthUserRepositoryInterface;
use App\Domain\User\Repositories\CreateUserRepositoryInterface;
use App\Domain\User\Repositories\SearchUserRepositoryInterface;
use App\Domain\User\Tasks\PreparePhoneTaskInterface;
use App\Infrastructure\Chat\Repositories\DestroyChatRepository;
use App\Infrastructure\Chat\Repositories\GetChatByIdRepository;
use App\Infrastructure\Chat\Repositories\GetOrCreateChatRepository;
use App\Infrastructure\User\Repositories\AuthUserRepository;
use App\Infrastructure\User\Repositories\CreateUserRepository;
use App\Infrastructure\User\Repositories\SearchUserRepository;
use App\Presentation\User\Controllers\LogoutController;
use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\ClientBuilder;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            RegisterActionInterface::class,
            RegisterAction::class
        );
        $this->app->bind(
            AuthorizationActionInterface::class,
            AuthorizationAction::class
        );
        $this->app->bind(
            PreparePhoneTaskInterface::class,
            PreparePhoneTask::class
        );
        $this->app->bind(
            CreateUserRepositoryInterface::class,
            CreateUserRepository::class
        );
        $this->app->bind(
            AuthUserRepositoryInterface::class,
            AuthUserRepository::class
        );
        $this->app->bind(
            LogoutActionInterface::class,
            LogoutAction::class
        );
        $this->app->bind(
            SearchUserRepositoryInterface::class,
            SearchUserRepository::class
        );
        $this->app->bind(
            GetOrCreateChatActionInterface::class,
            GetOrCreateChatAction::class
        );
        $this->app->bind(
            GetOrCreateChatRepositoryInterface::class,
            GetOrCreateChatRepository::class
        );
        $this->app->bind(
            DestroyChatRepositoryInterface::class,
            DestroyChatRepository::class
        );
        $this->app->bind(
            ChoiceStoreMessageStrategyInterface::class,
            ChoiceStoreMessageStrategy::class
        );
        $this->app->bind(
            GetChatByIdRepositoryInterface::class,
            GetChatByIdRepository::class
        );
        $this->app->bind(
            ChatTokenCryptoActionInterface::class,
            ChatTokenCryptoAction::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
