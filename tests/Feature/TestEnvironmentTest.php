<?php

use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;

it('boots with isolated test data and no external service credentials', function (): void {
    expect(app()->environment())->toBe('testing')
        ->and(app()->configurationIsCached())->toBeFalse()
        ->and(config('app.key'))->toBe('base64:iLpyleJnB/vL4edHQxHdbwYHTaKs66VFKkmNJIipe40=')
        ->and(config('app.url'))->toBe('http://localhost')
        ->and(config('database.default'))->toBe('sqlite')
        ->and(config('database.connections.sqlite.database'))->toBe(':memory:')
        ->and(config('database.connections.sqlite.url'))->toBe('')
        ->and(config('cache.default'))->toBe('array')
        ->and(config('session.driver'))->toBe('array')
        ->and(config('queue.default'))->toBe('sync')
        ->and(config('mail.default'))->toBe('array')
        ->and(config('broadcasting.default'))->toBeNull()
        ->and(config('filesystems.default'))->toBe('local');

    foreach (['services.postmark.key', 'services.resend.key', 'services.ses.key', 'services.ses.secret', 'services.slack.notifications.bot_user_oauth_token'] as $key) {
        expect(config($key))->toBe('');
    }

    DB::statement('CREATE TABLE isolation_probe (id INTEGER)');
    DB::statement('INSERT INTO isolation_probe (id) VALUES (1)');
    expect(DB::select('SELECT id FROM isolation_probe'))->toHaveCount(1);
    DB::purge();
    // A new connection gets a new database, not a file or a shared service.
    expect(DB::select("SELECT name FROM sqlite_master WHERE name = 'isolation_probe'"))->toBe([]);
});

it('overrides hostile inherited settings before booting the application', function (): void {
    $hostCache = tempnam(sys_get_temp_dir(), 'launch-host-config-');
    expect($hostCache)->not->toBeFalse();
    $cacheContents = '<?php throw new RuntimeException("Host configuration must not load");';
    file_put_contents($hostCache, $cacheContents);

    $process = new Process([
        PHP_BINARY, base_path('vendor/bin/pest'), '--no-tia',
        'tests/Feature/TestEnvironmentTest.php', '--filter=boots with isolated', '--compact',
    ], base_path(), [
        'APP_ENV' => 'production',
        'APP_KEY' => 'not-a-real-production-key',
        'APP_CONFIG_CACHE' => $hostCache,
        'APP_URL' => 'https://invalid.invalid',
        'DB_CONNECTION' => 'mysql',
        'DB_DATABASE' => 'not-a-real-production-database',
        'DB_URL' => 'mysql://fake:fake@invalid.invalid/fake',
        'CACHE_STORE' => 'redis',
        'SESSION_DRIVER' => 'redis',
        'QUEUE_CONNECTION' => 'redis',
        'MAIL_MAILER' => 'ses',
        'BROADCAST_CONNECTION' => 'pusher',
        'FILESYSTEM_DISK' => 's3',
        'AWS_ACCESS_KEY_ID' => 'fake-host-key',
        'AWS_SECRET_ACCESS_KEY' => 'fake-host-secret',
        'POSTMARK_API_KEY' => 'fake-host-key',
        'RESEND_API_KEY' => 'fake-host-key',
        'SLACK_BOT_USER_OAUTH_TOKEN' => 'fake-host-token',
    ]);
    $process->setTimeout(60);
    try {
        $process->mustRun();

        expect($process->getOutput())->toContain('1 passed')
            ->and(file_get_contents($hostCache))->toBe($cacheContents);
    } finally {
        unlink($hostCache);
    }
});
