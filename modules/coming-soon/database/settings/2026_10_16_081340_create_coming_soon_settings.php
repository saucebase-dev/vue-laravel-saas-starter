<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('coming_soon.enabled', false);
        $this->migrator->add('coming_soon.title', 'Coming soon');
        $this->migrator->add('coming_soon.message', 'Our site is under construction. Please check back soon.');
    }
};
