<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

/** @return array<string, string|false> */
function agentContextCheckoutSnapshot(): array
{
    $git = new Process(['git', 'ls-files', '-z'], base_path());
    $git->mustRun();

    $paths = explode("\0", trim($git->getOutput(), "\0"));
    $paths[] = 'scripts/AgentContextRefresher.php';
    $paths[] = '.mcp.json';
    $snapshot = [];
    foreach ($paths as $path) {
        $absolute = base_path($path);
        $snapshot[$path] = is_link($absolute) ? readlink($absolute) : (is_file($absolute) ? hash_file('sha256', $absolute) : false);
    }

    ksort($snapshot);

    return $snapshot;
}

// Fault injection changes only the fixture's publisher, never production code
// or environment variables understood by the real entrypoint.
function agentContextInjectPublisher(string $root, string $method, string $body): void
{
    $path = $root.'/scripts/AgentContextRefresher.php';
    $source = str_replace('class AgentContextRefresher', 'class OriginalRefresher', File::get($path));
    File::put($path, $source.'
class AgentContextRefresher extends OriginalRefresher {
        protected function '.$method.' { '.$body.' }
    }');
}

function agentContextFixture(): string
{
    $root = sys_get_temp_dir().'/launch-context-'.bin2hex(random_bytes(8));
    File::makeDirectory($root, 0755, true);
    foreach (['app', 'config', 'routes', '.ai', '.agents', '.codex'] as $directory) {
        File::copyDirectory(base_path($directory), $root.'/'.$directory);
    }

    foreach (['bootstrap/cache', 'scripts', 'storage/framework/cache', 'storage/framework/views', 'storage/logs', '.claude'] as $directory) {
        File::makeDirectory($root.'/'.$directory, 0755, true);
    }

    foreach (['composer.json', 'composer.lock', 'package.json', 'vite.config.ts', 'boost.json', 'AGENTS.md', 'bootstrap/app.php', 'bootstrap/providers.php', 'scripts/update-agent-context.php', 'scripts/AgentContextRefresher.php'] as $path) {
        File::copy(base_path($path), $root.'/'.$path);
    }

    symlink(base_path('vendor'), $root.'/vendor');
    symlink('AGENTS.md', $root.'/CLAUDE.md');
    symlink('../.agents/skills', $root.'/.claude/skills');
    File::put($root.'/.mcp.json', '{"mcpServers":{"fixture":{"command":"preserve-me"}}}');

    return $root;
}

/** @return array<string, string|false> */
function agentContextProtectedSnapshot(string $root): array
{
    $snapshot = [];
    foreach (['AGENTS.md', 'CLAUDE.md', 'boost.json', '.mcp.json', '.claude/skills'] as $path) {
        $absolute = $root.'/'.$path;
        $snapshot[$path] = is_link($absolute) ? readlink($absolute) : hash_file('sha256', $absolute);
    }

    foreach (['.ai/rules', '.agents', '.codex'] as $directory) {
        foreach (File::allFiles($root.'/'.$directory, true) as $file) {
            $snapshot[$directory.'/'.$file->getRelativePathname()] = hash_file('sha256', $file->getPathname());
        }
    }

    ksort($snapshot);

    return $snapshot;
}
