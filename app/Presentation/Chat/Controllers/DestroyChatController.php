<?php

namespace App\Presentation\Chat\Controllers;

use App\Common\Controllers\Controller;
use App\Domain\Chat\Repositories\DestroyChatRepositoryInterface;
use App\Presentation\Chat\Requests\DestroyChatRequest;

class DestroyChatController extends Controller
{
    public function __construct(
        private readonly DestroyChatRepositoryInterface $repository
    )
    {
    }

    public function __invoke(DestroyChatRequest $request)
    {
        $res = $this->repository->exec($request->chat);
        if($res){
            return response()->noContent(201);
        }else{
            return response()->json([
                'error' => 'Chat not found'
            ])->status(404);
        }
    }
}
