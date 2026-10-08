<?php

declare(strict_types=1);

it('runs tests on in-memory SQLite and array drivers without service credentials', function (): void {
    expect(app()->environment())->toBe('testing')
        ->and(config('database.default'))->toBe('sqlite')
        ->and(config('database.connections.sqlite.database'))->toBe(':memory:')
        ->and(config('cache.default'))->toBe('array')
        ->and(config('session.driver'))->toBe('array')
        ->and(config('queue.default'))->toBe('sync')
        ->and(config('mail.default'))->toBe('array')
        ->and(config('filesystems.default'))->toBe('local')
        ->and(config('services.ses.key'))->toBeEmpty()
        ->and(config('services.postmark.key'))->toBeEmpty()
        ->and(config('services.resend.key'))->toBeEmpty()
        ->and(config('services.slack.notifications.bot_user_oauth_token'))->toBeEmpty();
});

it('never loads a cached configuration file', function (): void {
    // A leftover bootstrap/cache/config.php from a live checkout would carry its credentials.
    expect(app()->getCachedConfigPath())->toStartWith(storage_path('framework/cache/test-config-'))
        ->and(app()->configurationIsCached())->toBeFalse();
});
