<?php

namespace App\Presentation\Home\Controllers;

use App\Common\Controllers\Controller;
use App\Domain\Chat\Repositories\AddRecipientToChatsRepositoryInterface;
use App\Domain\Chat\Repositories\PaginateChatsRepositoryInterface;
use App\Domain\User\Repositories\GetUserByIdRepositoryInterface;
use Inertia\Inertia;

class HomePageController extends Controller
{
    public function __construct(
        readonly private PaginateChatsRepositoryInterface       $paginateChatsAction,
        readonly private AddRecipientToChatsRepositoryInterface $addRecipientToChatsAction,
        readonly private GetUserByIdRepositoryInterface $getUserByIdRepository,
    )
    {
    }

    public function __invoke()
    {
        $userId = auth()->id();
        $userData = $this->getUserByIdRepository->exec($userId);
        $userChats = $this->paginateChatsAction->exec(1,$userId);
        if($userChats->isEmpty()){
            return Inertia::render('Home/Home',[
                'title' => 'Home',
                'user_id' => $userId,
                'chats' => [],
                'user_data' => $userData,
            ]);
        }
        $userChatsWithRecipients = $this->addRecipientToChatsAction->exec(collect($userChats), $userId);
        return Inertia::render('Home/Home',[
            'title' => 'Home',
            'user_id' => $userId,
            'chats' => $userChatsWithRecipients,
            'user_data' => $userData,
        ]);
    }
}
