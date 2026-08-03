<?php

declare(strict_types=1);

describe('appearance defaults', function (): void {
    it('renders new visitors in light mode', function (): void {
        $response = $this->get('/');

        $response
            ->assertOk()
            ->assertSee("var appearance = 'light';", escape: false)
            ->assertDontSee('<html lang="en" class="dark">', escape: false);
    });

    it('migrates the legacy system default to light mode', function (): void {
        $response = $this->withCookie('appearance', 'system')->get('/');

        $response
            ->assertOk()
            ->assertSee("var appearance = 'light';", escape: false)
            ->assertDontSee("var appearance = 'system';", escape: false);
    });

    it('initializes the client preference as light', function (): void {
        $hook = file_get_contents(resource_path('js/hooks/use-appearance.tsx'));

        expect($hook)
            ->toContain('let currentAppearance: Appearance = "light";')
            ->toContain('return (localStorage.getItem("appearance") as Appearance) || "light";')
            ->toContain('localStorage.getItem("appearance") === "system"')
            ->toContain('localStorage.setItem("appearance", "light");')
            ->toContain('setCookie("appearance", "light");');
    });
});
