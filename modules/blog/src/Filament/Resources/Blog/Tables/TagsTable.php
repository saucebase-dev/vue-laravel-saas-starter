<?php

namespace Modules\Blog\Filament\Resources\Blog\Tables;

use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\Blog\Filament\Resources\Blog\PostResource;
use Modules\Blog\Models\Tag;

class TagsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('slug')
                    ->searchable(),

                TextColumn::make('posts_count')
                    ->label(__('Posts'))
                    ->counts('posts')
                    ->sortable()
                    ->url(fn (Tag $record): string => PostResource::getUrl('index', [
                        'filters' => ['tags' => ['values' => [$record->id]]],
                    ])),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }
}
