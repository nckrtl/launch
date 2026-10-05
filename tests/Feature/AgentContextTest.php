<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

require_once __DIR__.'/../Support/AgentContextFixtures.php';

it('shares instructions and skills without agent-specific copies', function (): void {
    expect(readlink(base_path('CLAUDE.md')))->toBe('AGENTS.md')
        ->and(realpath(base_path('.claude/skills')))->toBe(realpath(base_path('.agents/skills')))
        ->and(File::get(base_path('AGENTS.md')))->toContain('.ai/rules/index.md', '.ai/package-guidelines.md')
        ->and(File::get(base_path('.agents/skills/vite-plus/SKILL.md')))->toContain('defineLaunchConfig()')
        ->not->toContain('defineCraftConfig()');
});

it('refreshes selected package guidance without changing project rules or skills', function (): void {
    $boost = json_decode(File::get(base_path('boost.json')), true, flags: JSON_THROW_ON_ERROR);
    $composer = json_decode(File::get(base_path('composer.json')), true, flags: JSON_THROW_ON_ERROR);

    expect($boost['packages'])->toBe(['nckrtl/launch-laravel'])
        ->and($boost['mcp'])->toBeFalse()
        ->and($boost['skills'] ?? [])->toBe([])
        ->and($composer['scripts']['post-update-cmd'])->toContain('@php scripts/update-agent-context.php')
        ->and(config('boost.agents.claude_code.guidelines_path'))->toBe('.ai/package-guidelines.md')
        ->and(config('boost.agents.codex.guidelines_path'))->toBe('.ai/package-guidelines.md');

    $checkout = agentContextCheckoutSnapshot();
    $fixture = agentContextFixture();
    try {
        // Force runtime drift regardless of whether this suite runs on PHP 8.4 or 8.5.
        $path = $fixture.'/.ai/package-guidelines.md';
        File::put($path, str_replace('running on PHP 8.5', 'running on PHP 0.0', File::get($path)));
        $protected = agentContextProtectedSnapshot($fixture);
        $process = new Process([PHP_BINARY, 'scripts/update-agent-context.php'], $fixture, ['COMPOSER_DEV_MODE' => '1', 'APP_ENV' => 'local']);
        $process->setTimeout(60);
        $process->mustRun();

        expect(File::get($path))->toContain('running on PHP '.PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION)
            ->and(agentContextProtectedSnapshot($fixture))->toBe($protected)
            ->and(agentContextCheckoutSnapshot())->toBe($checkout);
    } finally {
        File::deleteDirectory($fixture);
    }
});

it('skips Boost refresh on production installs', function (): void {
    $path = base_path('.ai/package-guidelines.md');
    $hash = hash_file('sha256', $path);
    $process = new Process([PHP_BINARY, 'scripts/update-agent-context.php'], base_path(), ['COMPOSER_DEV_MODE' => '0']);
    $process->mustRun();

    expect($process->getOutput())->toBe('')
        ->and(hash_file('sha256', $path))->toBe($hash);
});

it('exposes Boost discovery over the configured stdio MCP protocol', function (): void {
    $config = json_decode(File::get(base_path('.mcp.example.json')), true, flags: JSON_THROW_ON_ERROR);
    $server = $config['mcpServers']['laravel-boost'];
    expect($server)->toBe(['command' => 'php', 'args' => ['artisan', 'boost:mcp']])
        ->and(File::get(base_path('.codex/config.toml')))->toContain('[mcp_servers.laravel-boost]', 'args = ["artisan", "boost:mcp"]');

    $messages = [
        ['jsonrpc' => '2.0', 'id' => 1, 'method' => 'initialize', 'params' => [
            'protocolVersion' => '2025-11-25', 'capabilities' => new stdClass,
            'clientInfo' => ['name' => 'context-test', 'version' => '1'],
        ]],
        ['jsonrpc' => '2.0', 'method' => 'notifications/initialized'],
        ['jsonrpc' => '2.0', 'id' => 2, 'method' => 'tools/list'],
    ];
    $input = implode("\n", array_map(fn (array $message): string => json_encode($message, JSON_THROW_ON_ERROR), $messages))."\n";
    $process = new Process([PHP_BINARY, ...$server['args']], base_path(), ['APP_ENV' => 'local']);
    $process->setInput($input);
    $process->setTimeout(30);
    $process->mustRun();
    $responses = array_map(fn (string $line): array => json_decode($line, true, flags: JSON_THROW_ON_ERROR), explode("\n", trim($process->getOutput())));

    expect($responses[0]['result']['protocolVersion'])->toBe('2025-11-25')
        ->and(array_column($responses[1]['result']['tools'], 'name'))->toContain('search-docs', 'application-info');
});
