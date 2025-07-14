<?php

namespace App\Presentation\User\Controllers;

use App\Domain\User\Actions\LogoutActionInterface;
use Inertia\Inertia;

class LogoutController
{
    private LogoutActionInterface $logoutAction;
    public function __construct(LogoutActionInterface $logoutAction)
    {
        $this->logoutAction = $logoutAction;
    }

    public function __invoke()
    {
        $res = $this->logoutAction->exec();
        if($res){
            return Inertia::render('User/Authorization');
        }
        return Inertia::render('User/Authorization',[
            'logout_error' => 'You not authorized'
        ]);
    }
}
