<?php

namespace Modules\ComingSoon\Filament\Pages;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Modules\ComingSoon\Settings\ComingSoonSettings as Settings;
use Saucebase\Core\Filament\Pages\SettingsPage;

class ComingSoonSettings extends SettingsPage
{
    protected static string $settings = Settings::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    public static function getNavigationLabel(): string
    {
        return __('Coming Soon');
    }

    public function getTitle(): string
    {
        return __('Coming Soon');
    }

    public function getSaveFormAction(): Action
    {
        return parent::getSaveFormAction()->extraAttributes(['data-testid' => 'coming-soon-save']);
    }

    public function form(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Toggle::make('enabled')
                ->label(__('Enable Coming Soon'))
                ->extraAttributes(['data-testid' => 'coming-soon-enabled']),
            TextInput::make('title')
                ->label(__('Title'))
                ->required()
                ->extraInputAttributes(['data-testid' => 'coming-soon-title-input']),
            Textarea::make('message')
                ->label(__('Message'))
                ->required()
                ->rows(4)
                ->extraInputAttributes(['data-testid' => 'coming-soon-message-input']),
        ]);
    }
}
