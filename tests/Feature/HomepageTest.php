<?php

use Inertia\Testing\AssertableInertia as Assert;

it('renders the homepage with the Home inertia component', function (): void {
    $this->get('/')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('Home')
        );
});
