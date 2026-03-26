<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use NckRtl\Toolbar\Data\Layout\GroupConfig;
use NckRtl\Toolbar\Data\Layout\LayoutConfig;
use NckRtl\Toolbar\Data\ToolbarConfig;
use NckRtl\Toolbar\Data\Tools\AgentationTool;
use NckRtl\Toolbar\Enums\Layout\Section;
use NckRtl\Toolbar\Toolbar;

class ToolbarConfigProvider extends ServiceProvider
{
    public function register(): void
    {
        if (! class_exists(Toolbar::class)) {
            return;
        }
    }

    public function boot(): void
    {
        if (! class_exists(Toolbar::class)) {
            return;
        }

        if (! $this->app->bound(Toolbar::class)) {
            return;
        }

        $toolbar = $this->app->make(Toolbar::class);
        $this->update($toolbar->config);
    }

    public function update(ToolbarConfig $toolbarConfig): void
    {
        $toolbarConfig->layout(function (LayoutConfig $layout) {
            $layout->addGroup(
                (new GroupConfig(priority: 20))
                    ->addTool(new AgentationTool)
                    ->section(Section::CENTER)
            );
        });
    }
}
