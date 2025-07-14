<?php

namespace App\Presentation\Message\Controllers;

use App\Application\Message\DTOs\StoreMessageDTO;
use App\Application\Message\Jobs\StoreStringMessageJob;
use App\Common\Controllers\Controller;
use App\Domain\Message\Actions\ChoiceStoreMessageStrategyInterface;
use App\Presentation\Message\Requests\StoreMessageRequest;
use Illuminate\Support\Facades\Auth;

class StoreMessageController extends Controller
{
    public function __invoke(StoreMessageRequest $request,ChoiceStoreMessageStrategyInterface $choiceStoreMessageStrategy)
    {
        $dto = StoreMessageDTO::fromRequest($request->toArray());
        $userId = Auth::user()->id;
        $res = $choiceStoreMessageStrategy->exec($dto,$userId);
        if($res){
            return response()->noContent(204);
        }else{
            return response()->json([
                'SendMessageError' => 'Message type not found'
            ])->status(500);
        }
    }
}
