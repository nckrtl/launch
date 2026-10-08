<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php55\Rector\String_\StringClassNameToClassConstantRector;

// Workspace-local caches: a shared /tmp cache can leak between checkouts.
$containerCache = __DIR__.'/storage/framework/cache/rector-container';
if (! is_dir($containerCache)) {
    mkdir($containerCache, 0775, true);
}

return RectorConfig::configure()
    ->withCache(
        cacheDirectory: __DIR__.'/storage/framework/cache/rector',
        containerCacheDirectory: $containerCache,
    )
    ->withPaths([
        __DIR__.'/app',
        __DIR__.'/bootstrap',
        __DIR__.'/config',
        __DIR__.'/public',
        __DIR__.'/resources',
        __DIR__.'/routes',
        __DIR__.'/tests',
    ])
    ->withSkip([
        // Laravel's generated package/service manifests are not application source.
        __DIR__.'/bootstrap/cache',
        __DIR__.'/bootstrap/ssr',
        // The optional Agentation addon is probed by class-name string; it need not be installed.
        StringClassNameToClassConstantRector::class => [__DIR__.'/app/Providers/ToolbarConfigProvider.php'],
    ])
    ->withPhpSets()
    ->withTypeCoverageLevel(50)
    ->withDeadCodeLevel(50)
    ->withCodeQualityLevel(50)
    ->withCodingStyleLevel(25);
