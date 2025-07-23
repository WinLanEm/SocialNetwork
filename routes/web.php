<?php

use App\Domain\Message\Entities\Message;
use App\Presentation\Chat\Controllers\DestroyChatController;
use App\Presentation\Chat\Controllers\GetOrCreateChatController;
use App\Presentation\Chat\Controllers\StoreChatController;
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
use Illuminate\Support\Facades\Route;

Route::get('/qwe', function () {
    return view('qwe');
});

Route::middleware('is_not_authorized')->group(function(){
    Route::get('/register',RegisterFormController::class)->name('register-form');
    Route::post('/register',RegisterController::class)->name('register');
    Route::get('/authorize',AuthorizationFormController::class)->name('authorization-form');
    Route::post('/authorize',AuthorizationController::class)->name('authorization');
});
Route::middleware('is_authorized')->group(function(){
   Route::get('/logout',LogoutController::class)->name('logout');
   Route::get('/',HomePageController::class)->name('home');
   Route::get('/search_users',SearchUsersController::class)->name('search-users');
});
Route::prefix('message')->middleware('is_authorized')->group(function(){
    Route::post('store',StoreMessageController::class)->name('send-message');
    Route::get('get_in_chat',PaginateChatMessagesController::class)->name('get-chat-messages');
    Route::post('mark_messages_is_read',MarkIsReadController::class)->name('mark-messages-as-read');
});
Route::prefix('chat')->middleware('is_authorized')->group(function(){
   Route::post('store',GetOrCreateChatController::class)->name('get-or-create-chat');
   Route::delete('destroy',DestroyChatController::class)->name('destroy-chat');
});
