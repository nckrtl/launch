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

it('ships committed git hooks that install themselves', function (): void {
    // `vp config` (run by `bun install` via the prepare script) points
    // core.hooksPath at VitePlus's dispatcher, which sources these files. They
    // must be committed and executable or the dispatcher silently exits 0 and
    // no hook runs at all.
    foreach (['pre-commit', 'pre-push'] as $hook) {
        $path = base_path('.vite-hooks/'.$hook);

        expect(File::exists($path))->toBeTrue(".vite-hooks/{$hook} must be committed")
            ->and(is_executable($path))->toBeTrue(".vite-hooks/{$hook} must be executable");
    }

    expect(File::get(base_path('.vite-hooks/pre-commit')))->toContain('vp staged');
});

it('isolates pre-push commands from Git arguments and stdin', function (): void {
    // Git invokes pre-push with the remote name and URL as arguments and pipes
    // the pushed refs on stdin. Composer would treat a stray remote name as its
    // own argument, so neither may reach it.
    $hook = File::get(base_path('.vite-hooks/pre-push'));

    expect($hook)
        ->toContain('composer test </dev/null')
        ->toContain('composer analyse </dev/null')
        ->not->toContain('"$@"');
});

it('does not reintroduce git config hooks, which cannot ship configured', function (): void {
    // hook.<name>.event needs Git 2.54+ and lives in per-clone git config, so it
    // can never be committed -- that is why it required eight manual commands.
    // setup.php also used to unset core.hooksPath, disabling the dispatcher.
    foreach (['README.md', 'setup.php', 'resources/markdown/create.md'] as $file) {
        expect(File::get(base_path($file)))
            ->not->toContain('hook.launch-')
            ->not->toContain('--unset core.hooksPath');
    }
});
