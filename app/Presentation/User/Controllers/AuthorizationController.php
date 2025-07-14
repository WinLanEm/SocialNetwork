<?php

namespace App\Presentation\User\Controllers;


use App\Application\User\DTOs\AuthorizationDTO;
use App\Common\Controllers\Controller;
use App\Domain\User\Actions\AuthorizationActionInterface;
use App\Presentation\User\Requests\AuthorizationRequest;

class AuthorizationController extends Controller
{
    private AuthorizationActionInterface $authorizationAction;
    public function __construct(AuthorizationActionInterface $authorizationAction)
    {
        $this->authorizationAction = $authorizationAction;
    }

    public function __invoke(AuthorizationRequest $request)
    {
        $dto = AuthorizationDTO::fromRequest($request->toArray());
        if(!$this->authorizationAction->exec($dto)){
            return redirect()->back()->withErrors(['password' => 'Invalid phone or password']);
        }else{
            return redirect()->route('home');
        }
    }
}
