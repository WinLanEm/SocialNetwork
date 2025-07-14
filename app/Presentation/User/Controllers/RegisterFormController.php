<?php

namespace App\Presentation\User\Controllers;

use App\Common\Controllers\Controller;
use Inertia\Inertia;

class RegisterFormController extends Controller
{
    public function __invoke()
    {
        return Inertia::render('User/Register',[
            'title' => 'Register'
        ]);
    }
}
