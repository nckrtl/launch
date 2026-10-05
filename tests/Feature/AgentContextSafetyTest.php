<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

require_once __DIR__.'/../Support/AgentContextFixtures.php';

it('rejects rendering failures without publishing or rewriting selections', function (): void {
    $checkout = agentContextCheckoutSnapshot();
    $fixture = agentContextFixture();
    try {
        $protected = agentContextProtectedSnapshot($fixture);
        $baseline = File::get($fixture.'/.ai/package-guidelines.md');
        File::makeDirectory($fixture.'/.ai/guidelines');
        File::put($fixture.'/.ai/guidelines/broken.blade.php', '@php throw new RuntimeException("injected render failure"); @endphp');
        $process = new Process([PHP_BINARY, 'scripts/update-agent-context.php'], $fixture, ['APP_ENV' => 'local', 'COMPOSER_DEV_MODE' => '1']);
        $process->run();

        expect($process->getExitCode())->toBe(1)
            ->and($process->getErrorOutput())->toContain('Boost guideline rendering failed')
            ->and(File::get($fixture.'/.ai/package-guidelines.md'))->toBe($baseline)
            ->and(agentContextProtectedSnapshot($fixture))->toBe($protected)
            ->and(agentContextCheckoutSnapshot())->toBe($checkout);
    } finally {
        File::deleteDirectory($fixture);
    }
});

it('propagates injected candidate failures while preserving valid context', function (string $method, string $body): void {
    $checkout = agentContextCheckoutSnapshot();
    $fixture = agentContextFixture();
    try {
        $baseline = File::get($fixture.'/.ai/package-guidelines.md');
        $protected = agentContextProtectedSnapshot($fixture);
        agentContextInjectPublisher($fixture, $method, $body);
        $process = new Process([PHP_BINARY, 'scripts/update-agent-context.php'], $fixture, ['APP_ENV' => 'local', 'COMPOSER_DEV_MODE' => '1']);
        $process->run();

        expect($process->getExitCode())->toBe(1)
            ->and($process->getErrorOutput())->toContain('Agent context refresh failed:')
            ->and(File::get($fixture.'/.ai/package-guidelines.md'))->toBe($baseline)
            ->and(agentContextProtectedSnapshot($fixture))->toBe($protected)
            ->and(glob($fixture.'/.ai/.package-guidelines-*'))->toBe([])
            ->and(agentContextCheckoutSnapshot())->toBe($checkout);
    } finally {
        File::deleteDirectory($fixture);
    }
})->with([
    'partial write throws' => ['writeCandidate(string $path, string $content): void', 'file_put_contents($path, "partial"); throw new RuntimeException("injected write failure");'],
    'short write reports success' => ['writeCandidate(string $path, string $content): void', 'file_put_contents($path, "partial");'],
    'rename fails' => ['publishCandidate(string $candidate, string $destination): void', 'throw new RuntimeException("injected rename failure");'],
]);

it('preserves the old published context when a refresh process is killed', function (string $method, string $body): void {
    $checkout = agentContextCheckoutSnapshot();
    $fixture = agentContextFixture();
    $process = null;
    try {
        $baseline = File::get($fixture.'/.ai/package-guidelines.md');
        $protected = agentContextProtectedSnapshot($fixture);
        agentContextInjectPublisher($fixture, $method, $body);
        $process = new Process([PHP_BINARY, 'scripts/update-agent-context.php'], $fixture, ['APP_ENV' => 'local', 'COMPOSER_DEV_MODE' => '1']);
        $process->setTimeout(10);
        $process->start();
        expect($process->waitUntil(fn (string $type, string $output): bool => str_contains($output, 'candidate-ready')))->toBeTrue();
        $process->signal(9);
        $process->wait();

        expect($process->isSuccessful())->toBeFalse()
            ->and(File::get($fixture.'/.ai/package-guidelines.md'))->toBe($baseline)
            ->and(agentContextProtectedSnapshot($fixture))->toBe($protected)
            ->and(agentContextCheckoutSnapshot())->toBe($checkout);
    } finally {
        $process?->stop();
        File::deleteDirectory($fixture);
    }
})->with([
    'during partial write' => ['writeCandidate(string $path, string $content): void', 'file_put_contents($path, "partial"); echo "candidate-ready"; sleep(30);'],
    'after validation before rename' => ['publishCandidate(string $candidate, string $destination): void', 'echo "candidate-ready"; sleep(30); parent::publishCandidate($candidate, $destination);'],
]);
