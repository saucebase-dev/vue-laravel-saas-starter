<?php

namespace Modules\ComingSoon\Filament;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Modules\ComingSoon\Filament\Pages\ComingSoonSettings;
use Saucebase\Core\Filament\ModulePlugin;

class ComingSoonPlugin implements Plugin
{
    use ModulePlugin;

    public function getModuleName(): string
    {
        return 'ComingSoon';
    }

    public function getId(): string
    {
        return 'coming-soon';
    }

    public function register(Panel $panel): void
    {
        $panel->pages([ComingSoonSettings::class]);
    }
}
