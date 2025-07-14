<?php

namespace App\Application\Chat\Actions;

use App\Application\Chat\DTOs\GetOrCreateChatDTO;
use App\Domain\Chat\Actions\GetOrCreateChatActionInterface;
use App\Domain\Chat\Repositories\GetOrCreateChatRepositoryInterface;
use Illuminate\Support\Facades\Auth;

class GetOrCreateChatAction implements GetOrCreateChatActionInterface
{
    public function __construct(private readonly GetOrCreateChatRepositoryInterface $repository)
    {
    }

    public function exec(GetOrCreateChatDTO $DTO):array
    {
        $userId = Auth::user()->id;
        $users = $DTO->userIds;
        if(!in_array($userId,$users)){
            $users[] = $userId;
        }
        return $this->repository->exec($users,$DTO->type);
    }
}
