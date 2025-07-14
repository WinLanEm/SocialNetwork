<?php

namespace App\Presentation\User\Controllers;


use App\Application\User\DTOs\RegisterDTO;
use App\Common\Controllers\Controller;
use App\Domain\User\Actions\RegisterActionInterface;
use App\Presentation\User\Requests\RegisterRequest;

class RegisterController extends Controller
{
    private RegisterActionInterface $registerAction;

    public function __construct(RegisterActionInterface $registerAction)
    {
        $this->registerAction = $registerAction;
    }

    public function __invoke(RegisterRequest $request)
    {
        $dto = RegisterDTO::fromRequest($request->toArray());
        $res = $this->registerAction->exec($dto);
        if($res){
            return redirect()->route('home');
        }else{
            return back()->withErrors(['server','Fault on server, try latter']);
        }
    }
}
