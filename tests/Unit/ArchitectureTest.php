<?php

declare(strict_types=1);

arch('application code does not depend on test or analysis tools')
    ->expect('App')
    ->not->toUse(['Tests', 'Pest', 'PHPUnit', 'Rector', 'PHPStan']);

arch('application code does not ship debugging calls')
    ->expect(['dd', 'dump', 'var_dump'])
    ->not->toBeUsed();

arch('controllers use the Controller suffix')
    ->expect('App\Http\Controllers')
    ->toHaveSuffix('Controller');
