<?php

namespace App\Presentation\User\Controllers;

use App\Common\Controllers\Controller;
use App\Domain\User\Repositories\UserUpdateRepositoryInterface;
use App\Presentation\User\Requests\UserUpdateRequest;
use Illuminate\Support\Facades\Log;

class UserUpdateController extends Controller
{
    public function __construct(
        readonly private UserUpdateRepositoryInterface $userUpdateRepository
    )
    {
    }

    public function __invoke(UserUpdateRequest $request)
    {
        $user = $this->userUpdateRepository->exec($request);
        return response()->json([
            'username' => $user->username,
            'bio' => $user->bio,
            'avatar_url' => $user->avatar_url,
            'phone' => $user->phone,
        ], 200);
    }
}
