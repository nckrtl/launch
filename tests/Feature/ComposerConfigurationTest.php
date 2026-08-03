<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;

it('runs Pest with test impact analysis', function (): void {
    $composer = json_decode(
        File::get(base_path('composer.json')),
        associative: true,
        flags: JSON_THROW_ON_ERROR,
    );

    expect($composer['scripts']['test'])
        ->toContain('@php vendor/bin/pest --tia');
});
