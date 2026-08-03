<?php

declare(strict_types=1);

use App\Providers\ToolbarConfigProvider;
use Illuminate\Support\Facades\File;
use NckRtl\Toolbar\Data\Layout\GroupConfig;
use NckRtl\Toolbar\Data\ToolbarConfig;
use NckRtl\Toolbar\Data\Tools\BreakpointIndicatorTool;
use NckRtl\Toolbar\Enums\Layout\Section;

it('uses a red primary color with white text', function (): void {
    $toolbarConfig = new ToolbarConfig;

    (new ToolbarConfigProvider($this->app))->update($toolbarConfig);

    expect($toolbarConfig->primaryColor)->toBe('#F53003')
        ->and($toolbarConfig->primaryTextColor)->toBe('#FFFFFF');
});

it('uses the breakpoint indicator in the right section instead of Agentation', function (): void {
    $toolbarConfig = new ToolbarConfig;

    (new ToolbarConfigProvider($this->app))->update($toolbarConfig);

    $rightTools = collect($toolbarConfig->layout->sections[Section::RIGHT->value])
        ->flatMap(fn (GroupConfig $group): array => array_values($group->tools));
    $tools = collect($toolbarConfig->layout->sections)
        ->flatten(1)
        ->flatMap(fn (GroupConfig $group): array => array_values($group->tools));

    expect($rightTools->contains(fn (object $tool): bool => $tool instanceof BreakpointIndicatorTool))
        ->toBeTrue()
        ->and($tools->contains(fn (object $tool): bool => $tool->component() === 'Agentation'))
        ->toBeFalse();

    $breakpointIndicator = $rightTools->first(
        fn (object $tool): bool => $tool instanceof BreakpointIndicatorTool
    );

    expect($breakpointIndicator->show_pixels)->toBeFalse();
});

it('does not install Agentation as a frontend dependency', function (): void {
    $package = json_decode(
        File::get(base_path('package.json')),
        associative: true,
        flags: JSON_THROW_ON_ERROR,
    );

    expect($package['dependencies'] ?? [])->not->toHaveKey('agentation')
        ->and($package['devDependencies'] ?? [])->not->toHaveKey('agentation');
});

it('disables the Agentation Vite integration', function (): void {
    expect(File::get(base_path('vite.config.ts')))
        ->toContain('agentation: false');
});

it('exposes Tailwind breakpoints to the toolbar', function (): void {
    expect(File::get(resource_path('css/theme.css')))
        ->toContain('@theme static')
        ->toContain('--breakpoint-sm: 40rem;')
        ->toContain('--breakpoint-md: 48rem;')
        ->toContain('--breakpoint-lg: 64rem;')
        ->toContain('--breakpoint-xl: 80rem;')
        ->toContain('--breakpoint-2xl: 96rem;');
});
