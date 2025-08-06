<?php

namespace App\Presentation\Message\Controllers;

use App\Common\Controllers\Controller;
use App\Domain\Chat\Repositories\GetChatByIdRepositoryInterface;
use App\Domain\Message\Repositories\PaginateChatMessagesRepositoryInterface;
use App\Presentation\Message\Requests\PaginateChatMessagesRequest;

class PaginateChatMessagesController extends Controller
{
    public function __construct(
        readonly private PaginateChatMessagesRepositoryInterface $paginateChatMessagesRepository,
        readonly private GetChatByIdRepositoryInterface $getChatByIdRepository
    )
    {
    }

    public function __invoke(PaginateChatMessagesRequest $request)
    {
        $chat = $this->getChatByIdRepository->exec($request->chat_id);
        $this->authorize('view-chat', $chat);
        $messages = $this->paginateChatMessagesRepository->exec($request->chat_id,$chat->secret_key,$request->page);
        if(!empty($messages)){
            return response()->json(['messages'  =>$messages],200);
        }else{
            return response()->json(['messages' => null],200);
        }
    }
}
