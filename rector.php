<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php55\Rector\String_\StringClassNameToClassConstantRector;

return RectorConfig::configure()
    ->withCache(
        cacheDirectory: __DIR__.'/storage/framework/cache/rector',
        containerCacheDirectory: __DIR__.'/storage/framework/cache',
    )
    ->withPaths([
        __DIR__.'/app',
        __DIR__.'/bootstrap',
        __DIR__.'/config',
        __DIR__.'/public',
        __DIR__.'/resources',
        __DIR__.'/routes',
        __DIR__.'/scripts',
        __DIR__.'/tests',
    ])
    // Preserve the optional addon's string probe: the class need not be installed.
    ->withSkip([
        // Generated package manifests and bundles are not application source.
        __DIR__.'/bootstrap/cache',
        __DIR__.'/bootstrap/ssr',
        __DIR__.'/public/build',
        StringClassNameToClassConstantRector::class => [__DIR__.'/app/Providers/ToolbarConfigProvider.php'],
    ])
    ->withPhpSets()
    ->withTypeCoverageLevel(50)
    ->withDeadCodeLevel(50)
    ->withCodeQualityLevel(50)
    ->withCodingStyleLevel(25);
