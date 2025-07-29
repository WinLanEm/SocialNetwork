<?php

use App\Presentation\Chat\Controllers\DestroyChatController;
use App\Presentation\Chat\Controllers\GetOrCreateChatController;
use App\Presentation\Home\Controllers\HomePageController;
use App\Presentation\Message\Controllers\MarkIsReadController;
use App\Presentation\Message\Controllers\PaginateChatMessagesController;
use App\Presentation\Message\Controllers\StoreMessageController;
use App\Presentation\User\Controllers\AuthorizationController;
use App\Presentation\User\Controllers\AuthorizationFormController;
use App\Presentation\User\Controllers\LogoutController;
use App\Presentation\User\Controllers\RegisterController;
use App\Presentation\User\Controllers\RegisterFormController;
use App\Presentation\User\Controllers\SearchUsersController;
use App\Presentation\User\Controllers\UpdateLastSeenController;
use App\Presentation\User\Controllers\UserUpdateController;
use Illuminate\Support\Facades\Route;

Route::middleware('is_not_authorized')->group(function(){
    Route::get('/register',RegisterFormController::class)->name('register-form');
    Route::post('/register',RegisterController::class)->name('register');
    Route::get('/authorize',AuthorizationFormController::class)->name('authorization-form');
    Route::post('/authorize',AuthorizationController::class)->name('authorization');
});
Route::middleware('is_authorized')->group(function(){
    Route::get('/',HomePageController::class)->name('home');
   Route::post('/logout',LogoutController::class)->name('logout');
   Route::get('/search/users',SearchUsersController::class)->name('search-users');
   Route::patch('/update/profile',UserUpdateController::class)->name('update-profile');
   Route::post('/update/avatar',UserUpdateController::class)->name('update-avatar');
   Route::post('/update/last_seen',UpdateLastSeenController::class)->name('update-last-seen');
});
Route::prefix('messages')->middleware('is_authorized')->group(function(){
    Route::post('/',StoreMessageController::class)->name('send-message');
    Route::post('mark_messages_is_read',MarkIsReadController::class)->name('mark-messages-as-read');
});
Route::prefix('chats')->middleware('is_authorized')->group(function(){
    Route::get('messages',PaginateChatMessagesController::class)->name('get-chat-messages');
    Route::post('/',GetOrCreateChatController::class)->name('get-or-create-chat');
    Route::delete('/{chat}',DestroyChatController::class)->name('destroy-chat');
});
