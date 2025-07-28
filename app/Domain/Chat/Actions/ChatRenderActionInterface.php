<?php

namespace App\Domain\Chat\Actions;

use App\Domain\Chat\Entities\Chat;
use App\Domain\Message\Entities\Message;

interface ChatRenderActionInterface
{
    public function renderForPrivateChat(array $members,Chat $chat,Message $message):void;
}
