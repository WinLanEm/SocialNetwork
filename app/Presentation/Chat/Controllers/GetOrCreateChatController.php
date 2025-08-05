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
        if(empty($res)){
            return response()->json([
                'status' => false,
                'message' => 'You do not have permission to see this chat'
            ],403);
        }else{
            return response()->json($res);
        }
    }
}
