<?php

it('serves llms.txt as plain text', function () {
    $this->get('/llms.txt')
        ->assertSuccessful()
        ->assertHeader('Content-Type', 'text/plain; charset=utf-8');
});

it('points agents from llms.txt to the full setup guide', function () {
    expect($this->get('/llms.txt')->getContent())
        ->toContain('https://launch.nckrtl.com/create.md');
});

it('serves the setup guide as markdown', function () {
    $this->get('/create.md')
        ->assertSuccessful()
        ->assertHeader('Content-Type', 'text/markdown; charset=utf-8');
});

it('includes the non-interactive setup commands in the guide', function () {
    $content = $this->get('/create.md')->getContent();

    expect($content)
        ->toContain('composer create-project')
        ->toContain('php artisan key:generate')
        ->toContain('bun run build')
        // `composer setup` is interactive, so agents must be steered away from it.
        ->toContain('Do not run `composer setup`');
});
