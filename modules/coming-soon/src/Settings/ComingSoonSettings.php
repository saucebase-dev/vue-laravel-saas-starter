<?php

namespace Modules\ComingSoon\Settings;

use Spatie\LaravelSettings\Settings;

class ComingSoonSettings extends Settings
{
    public bool $enabled;

    public string $title;

    public string $message;

    public static function group(): string
    {
        return 'coming_soon';
    }
}
