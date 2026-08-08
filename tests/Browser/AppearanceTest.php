<?php

it('renders light, dark, and system modes without javascript errors', function () {
    $page = visit('/');

    $page->assertSee('Launch your next idea')
        ->assertNoJavaScriptErrors()
        ->click('[aria-label^="Theme"]')
        ->assertNoJavaScriptErrors()
        ->click('text=Light')
        ->assertNoJavaScriptErrors()
        ->click('[aria-label^="Theme"]')
        ->assertNoJavaScriptErrors()
        ->click('text=Dark')
        ->assertNoJavaScriptErrors()
        ->click('[aria-label^="Theme"]')
        ->assertNoJavaScriptErrors()
        ->click('text=System')
        ->assertNoJavaScriptErrors();
});
