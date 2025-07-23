<?php

namespace App\Presentation\Message\Controllers;

use App\Application\Message\DTOs\StoreMessageDTO;
use App\Application\Message\Jobs\StoreMessageJob;
use App\Common\Controllers\Controller;
use App\Presentation\Message\Requests\StoreMessageRequest;
use Illuminate\Support\Facades\Log;

class StoreMessageController extends Controller
{
    public function __invoke(StoreMessageRequest $request)
    {
        $dto = StoreMessageDTO::fromRequest($request->validated());
        $userId = auth()->id();
        StoreMessageJob::dispatch($dto, $userId);
        return response()->noContent(201);
    }
}
