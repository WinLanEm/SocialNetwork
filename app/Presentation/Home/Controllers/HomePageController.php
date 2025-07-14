<?php

namespace App\Presentation\Home\Controllers;

use Inertia\Inertia;

class HomePageController
{
    public function __invoke()
    {
        return Inertia::render('Home/Home',[
            'title' => 'Home'
        ]);
    }
}
