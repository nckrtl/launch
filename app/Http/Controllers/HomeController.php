<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use HardImpact\Waymaker\Get;
use Inertia\Response;
use Inertia\ResponseFactory;

class HomeController extends Controller
{
    #[Get(uri: '/')]
    public function show(): ResponseFactory|Response
    {
        return inertia('Home');
    }
}
