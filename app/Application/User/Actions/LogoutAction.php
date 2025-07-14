<?php

namespace App\Application\User\Actions;

use App\Domain\User\Actions\LogoutActionInterface;
use Illuminate\Support\Facades\Auth;

class LogoutAction implements LogoutActionInterface
{
    public function exec():bool
    {
        if(auth()->user()){
            Auth::logout();
            return true;
        }else{
            return false;
        }
    }
}
