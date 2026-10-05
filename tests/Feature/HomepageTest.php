<?php

use App\Providers\ToolbarConfigProvider;
use Inertia\Testing\AssertableInertia;
use Inertia\Testing\AssertableInertia as Assert;
use NckRtl\Toolbar\Toolbar;

it('renders the homepage with the Home inertia component', function (): void {
    $this->get('/')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page): AssertableInertia => $page
            ->component('Home')
        );
});

it('keeps Agentation local when no sync server is configured', function (): void {
    // The toolbar, and with it the runtime, stays off in console contexts.
    app(Toolbar::class)->config->enabledInConsole = true;

    $this->get('/')
        ->assertSuccessful()
        ->assertSee('/_toolbar-agentation/agentation.js', escape: false)
        ->assertSee('"endpoint":null', escape: false)
        ->assertDontSee('http://localhost:4747', escape: false);
})->skip(
    fn (): bool => ! ToolbarConfigProvider::agentationAddonInstalled(),
    'nckrtl/laravel-toolbar-agentation is an optional local addon.',
);
