<?php

namespace App\Infrastructure\User\Repositories;

use App\Domain\Chat\Entities\Chat;
use App\Domain\Chat\Repositories\CacheChatsRepositoryInterface;
use App\Domain\User\Entities\User;
use App\Domain\User\Repositories\UserUpdateRepositoryInterface;
use App\Presentation\User\Requests\UserUpdateRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UserUpdateRepository implements UserUpdateRepositoryInterface
{
    public function __construct(
        readonly private CacheChatsRepositoryInterface $cacheChatsRepository
    )
    {
    }

    public function exec(UserUpdateRequest $request):User
    {
        $user = auth()->user();
        $this->invalidateChatsCache($user);
        if(!$request->exists('avatar_url')) {
            $user->update($request->validated());
            return $user->fresh();
        }else{
            $file = $request->file('avatar_url');
            $path = $file->store('avatars', 'public');
            $url = Storage::disk('public')->url($path);
            $user->update([
                'avatar_url' => $url,
                ...$request->only('username','bio','phone')
            ]);
            return $user->fresh();
        }
    }
    private function invalidateChatsCache($user)
    {
        $userId = (string)$user->id;
        $recipientIds = Chat::where('participants', $userId)
            ->get(['participants'])
            ->flatMap(function($chat) use ($userId) {
                return array_diff($chat->participants, [$userId]);
            })
            ->unique()
            ->values()
            ->all();
        if (!empty($recipientIds)) {
            $this->cacheChatsRepository->invalidateRecipientsCache([$user->id]);
            $this->cacheChatsRepository->invalidateRecipientsCache($recipientIds);
        }
    }
}
