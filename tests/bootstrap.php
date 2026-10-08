<?php

declare(strict_types=1);

require dirname(__DIR__).'/vendor/autoload.php';

// Inherited host values (for example from an Orbit unit) sit in $_SERVER, which Laravel
// reads before $_ENV. Copy every forced phpunit.xml value into all three sources.
$phpunit = simplexml_load_file(dirname(__DIR__).'/phpunit.xml');
if ($phpunit === false) {
    throw new RuntimeException('Unable to read phpunit.xml.');
}

foreach ($phpunit->php->env as $env) {
    if ((string) $env['force'] === 'true') {
        $name = (string) $env['name'];
        $value = (string) $env['value'];
        $_SERVER[$name] = $_ENV[$name] = $value;
        putenv("{$name}={$value}");
    }
}

// A unique, nonexistent path: tests never load a cached config file, which would
// carry the live checkout's credentials, and never need `config:clear` on it.
$configCache = dirname(__DIR__).'/storage/framework/cache/test-config-'.bin2hex(random_bytes(8)).'.php';
$_SERVER['APP_CONFIG_CACHE'] = $_ENV['APP_CONFIG_CACHE'] = $configCache;
putenv("APP_CONFIG_CACHE={$configCache}");
