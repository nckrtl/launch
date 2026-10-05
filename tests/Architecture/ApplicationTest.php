<?php

arch('application code does not depend on development tools or tests')
    ->expect('App')
    ->not->toUse(['Tests', 'Pest', 'PHPUnit', 'Rector', 'PHPStan']);

arch('application code does not leave debugging calls behind')
    ->expect('App')
    ->not->toUse(['dd', 'dump', 'var_dump', 'print_r', 'die']);

arch('controllers follow the controller naming convention')
    ->expect('App\Http\Controllers')
    ->toHaveSuffix('Controller');
