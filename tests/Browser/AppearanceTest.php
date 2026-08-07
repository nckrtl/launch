<?php

it('renders both light mode and dark mode without javascript errors', function () {
    $page = visit('/');

    $page->assertSee('Launch your next idea')
        ->assertNoJavaScriptErrors();
});
