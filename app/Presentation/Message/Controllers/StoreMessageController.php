<?php

namespace App\Presentation\Message\Controllers;

use App\Application\Message\DTOs\StoreMessageDTO;
use App\Application\Message\Jobs\StoreMessageJob;
use App\Common\Controllers\Controller;
use App\Domain\Chat\Repositories\GetChatByIdRepositoryInterface;
use App\Presentation\Message\Requests\StoreMessageRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class StoreMessageController extends Controller
{
    public function __construct(
        private GetChatByIdRepositoryInterface $getChatByIdRepository,
    )
    {
    }

    public function __invoke(StoreMessageRequest $request)
    {
        $dto = StoreMessageDTO::fromRequest($request->validated());
        $userId = auth()->id();
        $chat = $this->getChatByIdRepository->exec($dto->chatId);
        Gate::authorize('view-chat', $chat);
        StoreMessageJob::dispatch($dto, $userId);
        return response()->noContent(201);
    }
}
