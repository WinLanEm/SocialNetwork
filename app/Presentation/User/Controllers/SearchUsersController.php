<?php

namespace App\Presentation\User\Controllers;

use App\Common\Controllers\Controller;
use App\Domain\User\Repositories\SearchUserRepositoryInterface;
use Illuminate\Http\Request;

class SearchUsersController extends Controller
{
    private SearchUserRepositoryInterface $searchUserRepository;
    public function __construct(SearchUserRepositoryInterface $searchUserRepository)
    {
        $this->searchUserRepository = $searchUserRepository;
    }

    public function __invoke(Request $request)
    {
        $users = $this->searchUserRepository->exec($request->query('username'));
        return response()->json($users);
    }
}
