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

it('isolates configured pre-push commands from Git arguments', function (): void {
    $setup = File::get(base_path('setup.php'));
    $readme = File::get(base_path('README.md'));

    expect($setup)
        ->toContain("['craft-test', 'pre-push', \"sh -c 'composer test' --\"]")
        ->toContain("['craft-analyse', 'pre-push', \"sh -c 'composer analyse' --\"]")
        ->and($readme)
        ->toContain("hook.craft-test.command \"sh -c 'composer test' --\"")
        ->toContain("hook.craft-analyse.command \"sh -c 'composer analyse' --\"");
});
