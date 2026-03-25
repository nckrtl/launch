<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use HardImpact\Waymaker\Get;

class HomeController extends Controller
{
    #[Get(uri: '/')]
    public function show(): \Inertia\ResponseFactory|\Inertia\Response
    {
        return inertia('Home');
    }
}
