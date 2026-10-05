<?php

require dirname(__DIR__).'/vendor/autoload.php';

// PHPUnit's force="true" updates putenv/$_ENV, but not inherited $_SERVER values.
// Laravel's immutable dotenv repository reads $_SERVER first. Keep all three
// sources consistent before any application (including Pest's browser app) boots.
$configuration = simplexml_load_file(dirname(__DIR__).'/phpunit.xml');

if ($configuration === false) {
    throw new RuntimeException('Unable to load the test isolation configuration.');
}

foreach ($configuration->php->env as $variable) {
    if ((string) $variable['force'] === 'true') {
        $name = (string) $variable['name'];
        $_SERVER[$name] = $_ENV[$name] = (string) $variable['value'];
        putenv($name.'='.$variable['value']);
    }
}
