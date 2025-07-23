<?php

namespace App\Providers;

use App\Application\Chat\Actions\ChatTokenCryptoAction;
use App\Application\Chat\Actions\GetOrCreateChatAction;
use App\Application\ChatUnreadMessage\Repositories\StoreUnreadMessagesCountRepository;
use App\Application\Message\Actions\ChoiceStoreMessageStrategy;
use App\Application\Message\Actions\MessageCryptoAction;
use App\Application\User\Actions\AuthorizationAction;
use App\Application\User\Actions\LogoutAction;
use App\Application\User\Actions\RegisterAction;
use App\Application\User\Tasks\PreparePhoneTask;
use App\Domain\Chat\Actions\ChatTokenCryptoActionInterface;
use App\Domain\Chat\Actions\GetOrCreateChatActionInterface;
use App\Domain\Chat\Repositories\AddRecipientToChatsRepositoryInterface;
use App\Domain\Chat\Repositories\ChatIsReadRepositoryInterface;
use App\Domain\Chat\Repositories\DestroyChatRepositoryInterface;
use App\Domain\Chat\Repositories\GetChatByIdRepositoryInterface;
use App\Domain\Chat\Repositories\GetOrCreateChatRepositoryInterface;
use App\Domain\Chat\Repositories\PaginateChatsRepositoryInterface;
use App\Domain\ChatUnreadMessage\Repositories\StoreUnreadMessagesCountRepositoryInterface;
use App\Domain\Message\Actions\ChoiceStoreMessageStrategyInterface;
use App\Domain\Message\Actions\MessageCryptoActionInterface;
use App\Domain\Message\Repositories\DecrementUnreadMessagesRepositoryInterface;
use App\Domain\Message\Repositories\MakeMessagesIsReadRepositoryInterface;
use App\Domain\Message\Repositories\PaginateChatMessagesRepositoryInterface;
use App\Domain\Message\Repositories\ReadMessageByIdRepositoryInterface;
use App\Domain\User\Actions\AuthorizationActionInterface;
use App\Domain\User\Actions\LogoutActionInterface;
use App\Domain\User\Actions\RegisterActionInterface;
use App\Domain\User\Repositories\AuthUserRepositoryInterface;
use App\Domain\User\Repositories\CreateUserRepositoryInterface;
use App\Domain\User\Repositories\GetUserByIdRepositoryInterface;
use App\Domain\User\Repositories\SearchUserRepositoryInterface;
use App\Domain\User\Tasks\PreparePhoneTaskInterface;
use App\Infrastructure\Chat\Repositories\AddRecipientToChatsRepository;
use App\Infrastructure\Chat\Repositories\ChatIsReadRepository;
use App\Infrastructure\Chat\Repositories\DestroyChatRepository;
use App\Infrastructure\Chat\Repositories\GetChatByIdRepository;
use App\Infrastructure\Chat\Repositories\GetOrCreateChatRepository;
use App\Infrastructure\Chat\Repositories\PaginateChatsRepository;
use App\Infrastructure\Message\Repositories\DecrementUnreadMessagesRepository;
use App\Infrastructure\Message\Repositories\MakeMessagesIsReadRepository;
use App\Infrastructure\Message\Repositories\PaginateChatMessagesRepository;
use App\Infrastructure\Message\Repositories\ReadMessageByIdRepository;
use App\Infrastructure\User\Repositories\AuthUserRepository;
use App\Infrastructure\User\Repositories\CreateUserRepository;
use App\Infrastructure\User\Repositories\GetUserByIdRepository;
use App\Infrastructure\User\Repositories\SearchUserRepository;
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
            GetChatByIdRepositoryInterface::class,
            GetChatByIdRepository::class
        );
        $this->app->bind(
            ChatTokenCryptoActionInterface::class,
            ChatTokenCryptoAction::class
        );
        $this->app->bind(
            GetUserByIdRepositoryInterface::class,
            GetUserByIdRepository::class
        );
        $this->app->bind(
            AddRecipientToChatsRepositoryInterface::class,
            AddRecipientToChatsRepository::class
        );
        $this->app->bind(
            PaginateChatsRepositoryInterface::class,
            PaginateChatsRepository::class
        );
        $this->app->bind(
            MessageCryptoActionInterface::class,
            MessageCryptoAction::class
        );
        $this->app->bind(
            PaginateChatMessagesRepositoryInterface::class,
            PaginateChatMessagesRepository::class
        );
        $this->app->bind(
            StoreUnreadMessagesCountRepositoryInterface::class,
            StoreUnreadMessagesCountRepository::class
        );
        $this->app->bind(
            ChatIsReadRepositoryInterface::class,
            ChatIsReadRepository::class
        );
        $this->app->bind(
            MakeMessagesIsReadRepositoryInterface::class,
            MakeMessagesIsReadRepository::class
        );
        $this->app->bind(
            DecrementUnreadMessagesRepositoryInterface::class,
            DecrementUnreadMessagesRepository::class
        );
        $this->app->bind(
            MakeMessagesIsReadRepositoryInterface::class,
            MakeMessagesIsReadRepository::class
        );
        $this->app->bind(
            ReadMessageByIdRepositoryInterface::class,
            ReadMessageByIdRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

    }
}
