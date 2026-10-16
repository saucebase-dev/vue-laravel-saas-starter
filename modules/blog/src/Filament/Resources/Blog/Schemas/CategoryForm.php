<?php

namespace Modules\Blog\Filament\Resources\Blog\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Modules\Blog\Models\Category;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true),

                TextInput::make('slug')
                    ->nullable()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->notIn(Category::RESERVED_SLUGS)
                    ->placeholder(__('Auto-generated from name')),
            ]);
    }
}
