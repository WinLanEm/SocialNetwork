<?php

namespace App\Presentation\Chat\Controllers;

use App\Application\Chat\DTOs\GetOrCreateChatDTO;
use App\Common\Controllers\Controller;
use App\Domain\Chat\Actions\GetOrCreateChatActionInterface;
use App\Presentation\Chat\Requests\GetOrCreateChatRequest;

class GetOrCreateChatController extends Controller
{
    public function __construct(private readonly GetOrCreateChatActionInterface $action)
    {
    }

    public function __invoke(GetOrCreateChatRequest $request)
    {
        $dto = GetOrCreateChatDTO::fromRequest($request->toArray());
        $res = $this->action->exec($dto);
        if($res){
            return response()->json([
                'chat_id' => $res['chat_id'],
                'last_message' => $res['last_message']
            ]);
        }else{
            return response()->json([
                'error' => 'Server error, try again later'
            ])->status(500);
        }
    }
}
