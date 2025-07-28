<?php

namespace App\Presentation\User\Controllers;

use App\Common\Controllers\Controller;
use App\Domain\User\Repositories\UserUpdateRepositoryInterface;
use App\Infrastructure\User\Repositories\UserUpdateRepository;
use App\Presentation\User\Requests\UserUpdateRequest;

class UpdateUserAvatarController extends Controller
{
    public function __construct(
        readonly private UserUpdateRepositoryInterface $userUpdateRepository
    )
    {
    }

    public function __invoke(UserUpdateRequest $request)
    {
        $user =  $this->userUpdateRepository->exec($request);
        return response()->json([
            'avatar_url' => $user->avatar_url
        ],200);
    }
}
