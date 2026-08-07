<?php

it('renders the launch marketing homepage without javascript errors', function () {
    $page = visit('/');

    $page->assertSee('unicorn')
        ->assertSee('How it works')
        ->assertSee("What's inside")
        ->assertSee('launch-ui')
        ->assertSee('The stack')
        ->assertSee('Give your next idea')
        ->assertNoJavaScriptErrors();
});
