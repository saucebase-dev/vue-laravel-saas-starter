<?php

namespace Modules\ComingSoon\Providers;

use Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests;
use Illuminate\Contracts\Http\Kernel;
use Inertia\Inertia;
use Modules\ComingSoon\Filament\Pages\ComingSoonSettings as SettingsPage;
use Modules\ComingSoon\Http\Middleware\RedirectToComingSoon;
use Modules\ComingSoon\Settings\ComingSoonSettings;
use Saucebase\Core\Providers\ModuleServiceProvider;

class ComingSoonServiceProvider extends ModuleServiceProvider
{
    public function boot(): void
    {
        parent::boot();

        $kernel = $this->app->make(Kernel::class);
        $kernel->appendMiddlewareToGroup('web', RedirectToComingSoon::class);
        $kernel->addToMiddlewarePriorityBefore(AuthenticatesRequests::class, RedirectToComingSoon::class);
    }

    protected function shareInertiaData(): void
    {
        Inertia::share('comingSoon.showBanner', fn (): bool => (request()->user()?->can('manage settings') ?? false)
            && app(ComingSoonSettings::class)->enabled);
        Inertia::share('comingSoon.settingsUrl', fn (): ?string => request()->user()?->can('manage settings')
            && request()->user()->can('access admin panel') ? SettingsPage::getUrl() : null);
    }
}
