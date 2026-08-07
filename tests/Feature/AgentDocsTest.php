<?php

use Symfony\Component\Yaml\Yaml;

it('serves llms.txt as plain text', function () {
    $this->get('/llms.txt')
        ->assertSuccessful()
        ->assertHeader('Content-Type', 'text/plain; charset=utf-8');
});

it('points agents from llms.txt to the full setup guide', function () {
    expect($this->get('/llms.txt')->getContent())
        ->toContain('https://launch.nckrtl.com/create.md');
});

it('lists every local environment addendum in llms.txt', function () {
    expect($this->get('/llms.txt')->getContent())
        ->toContain('https://launch.nckrtl.com/herd.md')
        ->toContain('https://launch.nckrtl.com/orbit.md')
        ->toContain('https://launch.nckrtl.com/solo.md');
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
        ->toContain('bun run build')
        // `composer setup` is interactive, so agents must be steered away from it.
        ->toContain('Do not run `composer setup`');
});

it('makes the guide branch on the detected environment before writing .env', function () {
    $content = $this->get('/create.md')->getContent();

    $detection = strpos($content, 'command -v orbit');
    $envConfig = strpos($content, 'VITE_APP_URL=http://localhost:8000');

    expect($detection)->not->toBeFalse()
        ->and($envConfig)->not->toBeFalse()
        // Detection has to come first, or the agent writes the wrong URLs and redoes the work.
        ->and($detection)->toBeLessThan($envConfig);

    expect($content)
        ->toContain('https://launch.nckrtl.com/herd.md')
        ->toContain('https://launch.nckrtl.com/orbit.md');
});

it('warns that composer dev never exits', function () {
    expect($this->get('/create.md')->getContent())
        ->toContain('It does not exit');
});

it('serves an addendum per supported environment', function (string $uri, string $mustContain) {
    $response = $this->get($uri);

    $response->assertSuccessful()
        ->assertHeader('Content-Type', 'text/markdown; charset=utf-8');

    expect($response->getContent())->toContain($mustContain);
})->with([
    ['/herd.md', 'herd secure'],
    ['/orbit.md', 'orbit instance:register'],
    ['/solo.md', 'mcp__solo__start_all_commands'],
]);

it('tells agents to detect Solo from their tool list, not the shell', function () {
    expect($this->get('/solo.md')->getContent())
        ->toContain('mcp__solo__')
        ->toContain('not a shell check');

    expect($this->get('/create.md')->getContent())->toContain('mcp__solo__');
});

it('ships solo.yml with no processes so Orbit can own them', function () {
    $solo = Yaml::parseFile(base_path('solo.yml'));

    // Orbit's runtime units inject VITE_DEV_SERVER_KEY/CERT. Declaring the same
    // commands here would start them a second time without those variables, so
    // the kit cannot pre-configure them without knowing whether Orbit is present.
    expect($solo['processes'])->toBe([]);
});

it('gives process ownership to Orbit whenever Orbit is present', function () {
    expect($this->get('/solo.md')->getContent())
        ->toContain('VITE_DEV_SERVER_KEY')
        ->toContain('command -v orbit');

    expect($this->get('/orbit.md')->getContent())
        ->toContain('orbit process:add')
        ->toContain('VITE_DEV_SERVER_CERT')
        // Orbit users must not duplicate the same commands in solo.yml.
        ->toContain('solo.yml');

    expect($this->get('/create.md')->getContent())
        ->toContain('orbit process:add')
        ->toContain('processes: {}');
});

it('tells both environments not to start a second PHP server', function (string $uri) {
    expect($this->get($uri)->getContent())
        ->toContain('Do not run')
        ->toContain('php artisan serve')
        ->toContain('composer dev');
})->with(['/herd.md', '/orbit.md']);

it('does not send agents to the non-existent orbit link command', function () {
    foreach (['/create.md', '/herd.md'] as $uri) {
        expect($this->get($uri)->getContent())->not->toContain('orbit link');
    }

    // The Orbit page may only mention it to warn against it: `orbit link` prints the
    // help listing and exits 0, so an agent would read the failure as success.
    expect($this->get('/orbit.md')->getContent())
        ->toContain('There is no `orbit link` command');
});

it('advertises the setup guide to agents from the homepage head', function () {
    $this->get('/')
        ->assertSuccessful()
        ->assertSee('rel="alternate" type="text/markdown" href="/create.md"', escape: false);
});
