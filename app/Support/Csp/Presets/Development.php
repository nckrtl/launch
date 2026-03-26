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

        $toolbarDomain = 'laravel-toolbar.test';

        $policy
            ->add(Directive::CONNECT, [
                'wss://'.$appDomain.':*',
                'https://'.$appDomain.':*',
                'wss://'.$toolbarDomain.':*',
                'https://'.$toolbarDomain.':*',
            ])
            ->add(Directive::STYLE, [
                'https://'.$appDomain.':*',
                'https://'.$toolbarDomain.':*',
                Keyword::UNSAFE_INLINE,
            ])
            ->add(Directive::FRAME, 'https://'.$appDomain.':*')
            ->add(Directive::SCRIPT, [
                'https://'.$appDomain.':*',
                'https://'.$toolbarDomain.':*',
                Keyword::UNSAFE_INLINE,
            ])
            ->add(Directive::FONT, [
                'https://'.$appDomain.':*',
                'https://'.$toolbarDomain.':*',
            ])
            ->add(Directive::IMG, ['https://'.$appDomain.':*', 'data:']);
    }
}
