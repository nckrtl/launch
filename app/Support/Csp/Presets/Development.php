<?php

namespace App\Support\Csp\Presets;

use Spatie\Csp\Directive;
use Spatie\Csp\Keyword;
use Spatie\Csp\Policy;
use Spatie\Csp\Preset;

class Development implements Preset
{
    public function configure(Policy $policy): void
    {
        if (app()->environment('production')) {
            return;
        }

        /** @var string $appUrl */
        $appUrl = config('app.url');

        $appDomain = explode('://', $appUrl)[1];

        // In dev, Vite serves assets from various origins:
        // - https://react-starterkit.test:5173 (when server.origin is set)
        // - https://vite.test:5173 (wildcard cert first SAN)
        // - https://localhost:5173 (default fallback)
        // Allow all of them plus the app domain itself.
        // Vite may serve assets from its cert's first SAN (e.g. vite.test)
        // rather than the app hostname. Allow all possible origins.
        $viteOrigins = [
            'https://'.$appDomain.':*',
            'http://'.$appDomain.':*',
            'https://localhost:*',
            'http://localhost:*',
            'https://vite.test:*',
        ];

        $policy
            ->add(Directive::CONNECT, [
                ...$viteOrigins,
                'wss://localhost:*',
                'wss://'.$appDomain.':*',
            ])
            ->add(Directive::STYLE, [
                ...$viteOrigins,
                Keyword::UNSAFE_INLINE,
            ])
            ->add(Directive::FRAME, $viteOrigins)
            ->add(Directive::SCRIPT, [
                ...$viteOrigins,
                Keyword::UNSAFE_INLINE,
            ])
            ->add(Directive::FONT, $viteOrigins)
            ->add(Directive::IMG, [...$viteOrigins, 'data:']);
    }
}
