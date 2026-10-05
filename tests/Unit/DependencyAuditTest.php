<?php

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

function dependencyAuditFixture(): array
{
    return [
        'audit' => ['braces' => [[
            'url' => 'https://github.com/advisories/GHSA-vfj7-8cjw-p6xm',
            'severity' => 'high',
            'vulnerable_versions' => '<=3.0.3',
        ]]],
        'lock' => ['workspaces' => ['' => ['devDependencies' => ['shadcn' => '^4.21.0']]], 'packages' => [
            'shadcn' => ['shadcn@4.21.0', '', ['dependencies' => ['fast-glob' => '*', 'ts-morph' => '*']]],
            'ts-morph' => ['ts-morph@26.0.0', '', ['dependencies' => ['@ts-morph/common' => '*']]],
            '@ts-morph/common' => ['@ts-morph/common@0.27.0', '', ['dependencies' => ['fast-glob' => '*']]],
            'fast-glob' => ['fast-glob@3.3.3', '', ['dependencies' => ['micromatch' => '*']]],
            'micromatch' => ['micromatch@4.0.8', '', ['dependencies' => ['braces' => '*']]],
            'braces' => ['braces@3.0.3', '', (object) []],
        ]],
        'manifest' => ['dependencies' => (object) [], 'devDependencies' => ['shadcn' => '^4.21.0']],
        'registry' => ['versions' => ['3.0.3' => (object) []]],
        'now' => '2026-10-05T17:54:00Z',
    ];
}

function runDependencyAuditFixture(array $fixture): Process
{
    $source = <<<'JS'
import { enforceException } from './scripts/audit-js.mjs';
const input = await new Response(Bun.stdin.stream()).json();
try {
    console.log(enforceException(input.audit, input.lock, input.manifest, input.registry, new Date(input.now)));
} catch (error) {
    console.error(error.message);
    process.exit(1);
}
JS;
    $process = new Process(['bun', '--eval', $source], dirname(__DIR__, 2), input: json_encode($fixture, JSON_THROW_ON_ERROR));
    $process->mustRun();

    return $process;
}

test('the approved tooling exception succeeds while keeping the high risk visible', function (): void {
    $process = runDependencyAuditFixture(dependencyAuditFixture());

    expect($process->getOutput())
        ->toContain('UNRESOLVED HIGH RISK', 'GHSA-vfj7-8cjw-p6xm', 'Nick approved question 59', '2026-10-12T00:00:00Z')
        ->toContain('shadcn > fast-glob > micromatch > braces', 'shadcn > ts-morph > @ts-morph/common');
});

test('the exception fails closed when its approval or scope changes', function (Closure $mutate): void {
    $fixture = dependencyAuditFixture();
    $mutate($fixture);

    expect(fn (): Process => runDependencyAuditFixture($fixture))
        ->toThrow(ProcessFailedException::class);
})->with([
    'at fixed expiry' => [function (array &$fixture): void {
        $fixture['now'] = '2026-10-12T00:00:00Z';
    }],
    'after expiry' => [function (array &$fixture): void {
        $fixture['now'] = '2026-10-13T00:00:00Z';
    }],
    'invalid clock' => [function (array &$fixture): void {
        $fixture['now'] = 'not-a-date';
    }],
    'different advisory' => [function (array &$fixture): void {
        $fixture['audit']['braces'][0]['url'] = 'https://github.com/advisories/GHSA-other';
    }],
    'different severity' => [function (array &$fixture): void {
        $fixture['audit']['braces'][0]['severity'] = 'critical';
    }],
    'different range' => [function (array &$fixture): void {
        $fixture['audit']['braces'][0]['vulnerable_versions'] = '<=3.0.4';
    }],
    'additional package advisory' => [function (array &$fixture): void {
        $fixture['audit']['vitest'] = $fixture['audit']['braces'];
    }],
    'additional braces advisory' => [function (array &$fixture): void {
        $fixture['audit']['braces'][] = $fixture['audit']['braces'][0];
    }],
    'no advisory means remove exception' => [function (array &$fixture): void {
        $fixture['audit'] = (object) [];
    }],
    'different installed version' => [function (array &$fixture): void {
        $fixture['lock']['packages']['braces'][0] = 'braces@3.0.2';
    }],
    'additional braces instance' => [function (array &$fixture): void {
        $fixture['lock']['packages']['other/braces'] = $fixture['lock']['packages']['braces'];
    }],
    'aliased additional instance' => [function (array &$fixture): void {
        $fixture['lock']['packages']['braces-alias'] = $fixture['lock']['packages']['braces'];
    }],
    'runtime root' => [function (array &$fixture): void {
        $fixture['manifest']['dependencies'] = ['shadcn' => '*'];
    }],
    'additional declared workspace' => [function (array &$fixture): void {
        $fixture['manifest']['workspaces'] = ['apps/*'];
    }],
    'additional locked workspace' => [function (array &$fixture): void {
        $fixture['lock']['workspaces'] = ['' => [], 'apps/runtime' => []];
    }],
    'missing locked root' => [function (array &$fixture): void {
        unset($fixture['lock']['workspaces']);
    }],
    'locked runtime root' => [function (array &$fixture): void {
        $fixture['lock']['workspaces']['']['dependencies'] = ['shadcn' => '*'];
    }],
    'new locked root path' => [function (array &$fixture): void {
        $fixture['lock']['workspaces']['']['devDependencies']['micromatch'] = '*';
    }],
    'optional runtime root' => [function (array &$fixture): void {
        $fixture['manifest']['optionalDependencies'] = ['shadcn' => '*'];
    }],
    'peer runtime root' => [function (array &$fixture): void {
        $fixture['manifest']['peerDependencies'] = ['shadcn' => '*'];
    }],
    'new optional root path' => [function (array &$fixture): void {
        $fixture['manifest']['optionalDependencies'] = ['micromatch' => '*'];
    }],
    'new peer root path' => [function (array &$fixture): void {
        $fixture['manifest']['peerDependencies'] = ['micromatch' => '*'];
    }],
    'new root path' => [function (array &$fixture): void {
        $fixture['manifest']['devDependencies']['micromatch'] = '*';
    }],
    'changed tooling path' => [function (array &$fixture): void {
        unset($fixture['lock']['packages']['shadcn'][2]['dependencies']['ts-morph']);
    }],
    'new runtime dependency reaches braces' => [function (array &$fixture): void {
        $fixture['manifest']['dependencies'] = ['runtime' => '*'];
        $fixture['lock']['packages']['runtime'] = ['runtime@1.0.0', '', ['dependencies' => ['braces' => '*']]];
    }],
    'new optional path reaches braces' => [function (array &$fixture): void {
        $fixture['lock']['packages']['shadcn'][2]['optionalDependencies'] = ['braces' => '*'];
    }],
    'new peer path reaches braces' => [function (array &$fixture): void {
        $fixture['lock']['packages']['shadcn'][2]['peerDependencies'] = ['braces' => '*'];
    }],
    'new stable patch available' => [function (array &$fixture): void {
        $fixture['registry']['versions']['3.0.4'] = (object) [];
    }],
    'new stable major available' => [function (array &$fixture): void {
        $fixture['registry']['versions']['4.0.0'] = (object) [];
    }],
    'missing registry metadata' => [function (array &$fixture): void {
        $fixture['registry'] = (object) [];
    }],
]);

test('runtime builds reject braces modules including nested and Windows paths', function (string $id, bool $reject): void {
    $source = <<<'JS'
import { rejectRuntimeBraces } from './scripts/dependency-runtime-guard.ts';
const id = await new Response(Bun.stdin.stream()).text();
rejectRuntimeBraces(id);
JS;
    $process = new Process(['bun', '--eval', $source], dirname(__DIR__, 2), input: $id);
    $process->run();

    expect($process->isSuccessful())->toBe(! $reject);
})->with([
    ['/project/node_modules/braces/index.js', true],
    ['/project/node_modules/.bun/braces@3.0.3/node_modules/braces/lib/parse.js', true],
    ['C:\\project\\node_modules\\braces\\index.js', true],
    ['/project/resources/js/app.tsx', false],
    ['/project/node_modules/react/index.js', false],
    ['shadcn', true],
    ['shadcn/dist/index.js', true],
    ['fast-glob', true],
    ['micromatch', true],
    ['ts-morph', true],
    ['@ts-morph/common', true],
    ['braces', true],
    ['react', false],
]);

test('dependency safety gates remain part of the full check', function (): void {
    $root = dirname(__DIR__, 2);
    $composer = json_decode(file_get_contents($root.'/composer.json'), true, flags: JSON_THROW_ON_ERROR);

    expect($composer['scripts']['check'])->toContain('@audit:dependencies', '@test:full', '@test:browser --no-tia')
        ->and($composer['scripts']['audit:dependencies'])->toBe([
            '@composer audit --locked',
            'vendor/bin/vet --no-interaction',
            'bun scripts/audit-js.mjs',
        ])
        ->and(json_decode(file_get_contents($root.'/vet.json'), true, flags: JSON_THROW_ON_ERROR)['minimum-release-age'])->toBe(7)
        ->and(file_get_contents($root.'/bunfig.toml'))->toContain('minimumReleaseAge = 604800', 'minimumReleaseAgeExcludes = []')
        ->and(file_get_contents($root.'/vite.config.ts'))->toContain('...(config.plugins ?? []), runtimeDependencyGuard')
        ->and(file_get_contents($root.'/scripts/dependency-runtime-guard.ts'))->toContain('rejectRuntimeBraces(id)', '...module.importedIds', '...module.dynamicallyImportedIds');
});
