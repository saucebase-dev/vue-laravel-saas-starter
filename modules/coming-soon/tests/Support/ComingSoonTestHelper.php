<?php

namespace Modules\ComingSoon\Tests\Support;

use Modules\ComingSoon\Filament\Pages\ComingSoonSettings as SettingsPage;
use Modules\ComingSoon\Settings\ComingSoonSettings;

class ComingSoonTestHelper
{
    /** @return array{enabled: bool, title: string, message: string} */
    public static function snapshot(): array
    {
        $settings = app(ComingSoonSettings::class);

        return ['enabled' => $settings->enabled, 'title' => $settings->title, 'message' => $settings->message];
    }

    /** @param array{enabled: bool, title: string, message: string} $values */
    public static function restore(array $values): void
    {
        $settings = app(ComingSoonSettings::class);
        $settings->fill($values)->save();
    }

    /** @return array{page: string, settings: string} */
    public static function urls(): array
    {
        return ['page' => route('coming-soon.index'), 'settings' => SettingsPage::getUrl()];
    }
}
