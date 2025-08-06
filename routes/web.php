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
});
