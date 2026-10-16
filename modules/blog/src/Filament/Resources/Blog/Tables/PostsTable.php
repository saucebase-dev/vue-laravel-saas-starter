<?php

namespace Modules\Blog\Filament\Resources\Blog\Tables;

use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Modules\Blog\Enums\PostStatus;
use Modules\Blog\Filament\Resources\Blog\PostResource;

class PostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('cover')
                    ->label('')
                    ->collection('cover')
                    ->conversion('card')
                    ->imageHeight(40)
                    ->extraImgAttributes(['class' => 'aspect-video rounded object-cover']),

                TextColumn::make('title')
                    ->searchable()
                    ->limit(50),

                TextColumn::make('category.name')
                    ->placeholder(__('—'))
                    ->sortable(),

                TextColumn::make('tags.name')
                    ->badge()
                    ->placeholder(__('—'))
                    ->toggleable(),

                TextColumn::make('status')
                    ->badge()
                    ->sortable(),

                TextColumn::make('author.name')
                    ->placeholder(__('—'))
                    ->sortable(),

                TextColumn::make('published_at')
                    ->dateTime()
                    ->sortable()
                    ->placeholder(__('—')),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(PostStatus::class),
                SelectFilter::make('category')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('tags')
                    ->relationship('tags', 'name')
                    ->multiple()
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                PostResource::viewPostAction(),
                EditAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
