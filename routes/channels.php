<?php

use App\Domain\Chat\Entities\Chat;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;

Broadcast::channel('last_message.{userId}', function ($user, $userId) {
    return (int)$user->id === (int)$userId;
});
Broadcast::channel('message_in_chat.{chatId}', function ($user, $chatId) {
    $chat = Chat::find($chatId);
    if (!$chat) {
        return false;
    }
    return in_array($user->id,$chat->participants);
});
Broadcast::channel('chat_render_by_user.{userId}', function ($user, $userId) {
    return (int)$user->id === (int)$userId;
});
Broadcast::channel('fail_store_message_user.{userId}', function ($user, $userId) {
    return (int)$user->id === (int)$userId;
});
Broadcast::channel('read_messages_user.{userId}', function ($user, $userId) {
    return (int)$user->id === (int)$userId;
});
Broadcast::channel('make_chat_is_read_user.{userId}', function ($user, $userId) {
    return (int)$user->id === (int)$userId;
});
Broadcast::channel('send_real_id.{userId}', function ($user, $userId) {
    return (int)$user->id === (int)$userId;
});
Broadcast::channel('last_seen.{userId}', function ($user, $userId) {
    return true;
});

