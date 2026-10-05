<?php

declare(strict_types=1);

// Production installs do not include Boost. Do not invoke a missing dev command.
if (getenv('COMPOSER_DEV_MODE') === '0' || ! is_file(__DIR__.'/../vendor/laravel/boost/src/BoostServiceProvider.php')) {
    exit(0);
}

$command = escapeshellarg(PHP_BINARY).' '.escapeshellarg(__DIR__.'/../artisan')
    .' boost:update --no-discover --ignore-skills --no-interaction';

passthru($command, $status);

exit($status);
