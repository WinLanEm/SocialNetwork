<?php


use App\Presentation\Chat\Controllers\DestroyChatController;
use App\Presentation\Chat\Controllers\GetOrCreateChatController;
use App\Presentation\Message\Controllers\MarkIsReadController;
use App\Presentation\Message\Controllers\PaginateChatMessagesController;
use App\Presentation\Message\Controllers\StoreMessageController;
use App\Presentation\User\Controllers\ApiAuthorizationController;
use App\Presentation\User\Controllers\ApiLogoutController;
use App\Presentation\User\Controllers\SearchUsersController;
use App\Presentation\User\Controllers\UpdateLastSeenController;
use App\Presentation\User\Controllers\UserUpdateController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function(){
    Route::get('/search/users',SearchUsersController::class)->name('search-users');
    Route::patch('/update/profile',UserUpdateController::class)->name('update-profile');
    Route::post('/update/avatar',UserUpdateController::class)->name('update-avatar');
    Route::post('/update/last_seen',UpdateLastSeenController::class)->name('update-last-seen');


    Route::prefix('messages')->group(function(){
        Route::post('/',StoreMessageController::class)->name('send-message');
        Route::post('mark_messages_is_read',MarkIsReadController::class)->name('mark-messages-as-read');
    });

    Route::prefix('chats')->middleware('is_authorized')->group(function(){
        Route::get('messages',PaginateChatMessagesController::class)->name('get-chat-messages');
        Route::post('/',GetOrCreateChatController::class)->name('get-or-create-chat');
        Route::delete('/{chat}',DestroyChatController::class)->name('destroy-chat');
    });
});
