<?php

namespace App\Presentation\Chat\Controllers;

use App\Common\Controllers\Controller;
use App\Domain\Chat\Entities\Chat;
use App\Domain\Chat\Repositories\DestroyChatRepositoryInterface;
use App\Presentation\Chat\Requests\DestroyChatRequest;

class DestroyChatController extends Controller
{
    public function __construct(
        private readonly DestroyChatRepositoryInterface $repository
    )
    {
    }

    public function __invoke(string $chatId)
    {
        $deletedCount = $this->repository->exec($chatId);

        if ($deletedCount > 0) {
            return response()->noContent();
        }

        return response()->json([
            'error' => 'Chat not found'
        ], 404);
    }
}
