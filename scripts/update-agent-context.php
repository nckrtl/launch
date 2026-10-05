<?php

declare(strict_types=1);

use Illuminate\Contracts\Console\Kernel;
use Laravel\Boost\Install\GuidelineComposer;
use Laravel\Boost\Install\GuidelineConfig;
use Laravel\Boost\Support\Config;
use Laravel\Boost\Support\RenderFailures;
use Launch\AgentContextRefresher;

$root = dirname(__DIR__);

// Production installs do not include Boost. Do not invoke a missing dev command.
if (getenv('COMPOSER_DEV_MODE') === '0' || ! is_file($root.'/vendor/laravel/boost/src/BoostServiceProvider.php')) {
    exit(0);
}

try {
    require $root.'/vendor/autoload.php';
    require_once __DIR__.'/AgentContextRefresher.php';
    $app = require $root.'/bootstrap/app.php';
    $app->make(Kernel::class)->bootstrap();

    $selection = $app->make(Config::class);
    if (! $selection->isValid() || ! $selection->getGuidelines()) {
        throw new RuntimeException('Missing or disabled Boost guideline selection.');
    }

    $config = new GuidelineConfig;
    $config->aiGuidelines = $selection->getPackages();
    $config->enforceTests = true;
    $config->usesSail = $selection->getSail();
    $config->hasMcp = $selection->getMcp();

    // Do not call Boost's installer/writers: they swallow failures, truncate the
    // destination and rewrite boost.json. Compose only, with no rule extraction.
    $failures = $app->make(RenderFailures::class);
    $failures->flush();
    $content = $app->make(GuidelineComposer::class)->config($config)->withoutRuleExtraction()->compose();
    if (! $failures->isEmpty()) {
        throw new RuntimeException('Boost guideline rendering failed: '.implode(', ', $failures->paths()));
    }

    new AgentContextRefresher($root)->refresh("<laravel-boost-guidelines>\n".$content."\n\n</laravel-boost-guidelines>\n");
    fwrite(STDOUT, "Agent package guidance refreshed safely.\n");
} catch (Throwable $throwable) {
    fwrite(STDERR, 'Agent context refresh failed: '.$throwable->getMessage().PHP_EOL);
    exit(1);
}
