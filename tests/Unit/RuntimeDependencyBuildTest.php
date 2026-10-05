<?php

use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

test('real client and SSR builds reject static and dynamic tooling imports even when externalized', function (bool $ssr, string $source, bool $reject): void {
    $root = dirname(__DIR__, 2);
    $directory = sys_get_temp_dir().'/launch-runtime-audit-'.bin2hex(random_bytes(8));
    mkdir($directory);

    try {
        file_put_contents($directory.'/entry.js', $source);
        $guard = json_encode($root.'/scripts/dependency-runtime-guard.ts', JSON_THROW_ON_ERROR);
        $entry = json_encode($directory.'/entry.js', JSON_THROW_ON_ERROR);
        $build = $ssr ? "ssr: {$entry}," : '';
        file_put_contents($directory.'/vite.config.ts', <<<JS
import { runtimeDependencyGuard } from {$guard};
export default {
    root: {$entry}.slice(0, -9),
    publicDir: false,
    plugins: [runtimeDependencyGuard],
    build: {
        {$build}
        rollupOptions: { input: {$entry}, external: ['shadcn'] },
    },
};
JS);
        $process = new Process(['vp', 'build', '--config', $directory.'/vite.config.ts'], $root);
        $process->setTimeout(30);
        $process->run();
        $output = $process->getOutput().$process->getErrorOutput();

        expect($process->isSuccessful())->toBe(! $reject, $output);

        if ($reject) {
            expect($output)->toContain('reject-runtime-braces', 'forbidden in runtime modules');
        }
    } finally {
        new Filesystem()->deleteDirectory($directory);
    }
})->with([
    'client static external' => [false, 'import "shadcn"; export const render = () => "ok";', true],
    'client dynamic external' => [false, 'export const render = () => import("shadcn");', true],
    'SSR static external' => [true, 'import "shadcn"; export const render = () => "ok";', true],
    'SSR dynamic external' => [true, 'export const render = () => import("shadcn");', true],
    'client safe' => [false, 'export const render = () => "ok";', false],
    'SSR safe' => [true, 'export const render = () => "ok";', false],
]);
