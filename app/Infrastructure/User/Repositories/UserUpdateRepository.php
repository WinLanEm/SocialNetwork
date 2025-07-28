<?php

namespace App\Infrastructure\User\Repositories;

use App\Domain\User\Entities\User;
use App\Domain\User\Repositories\UserUpdateRepositoryInterface;
use App\Presentation\User\Requests\UserUpdateRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UserUpdateRepository implements UserUpdateRepositoryInterface
{
    public function exec(UserUpdateRequest $request):User
    {
        $user = auth()->user();
        if(!$request->exists('avatar_url')) {
            $user->update($request->validated());
            return $user->fresh();
        }else{
            $file = $request->file('avatar_url');
            $path = $file->store('avatars', 'public');
            $url = Storage::disk('public')->url($path);
            $user->update([
                'avatar_url' => $url,
            ]);
            return $user->fresh();
        }
    }
}
