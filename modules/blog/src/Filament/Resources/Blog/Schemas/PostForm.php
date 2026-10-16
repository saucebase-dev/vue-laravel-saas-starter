<?php

namespace Modules\Blog\Filament\Resources\Blog\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Modules\Blog\Enums\PostStatus;
use Modules\Blog\Models\Post;

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Group::make([
                    TextInput::make('title')
                        ->hiddenLabel()
                        ->placeholder(__('Add title'))
                        ->required()
                        ->maxLength(255)
                        ->extraInputAttributes(['class' => 'text-2xl font-semibold']),

                    TextInput::make('slug')
                        ->label(__('Slug'))
                        ->nullable()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true)
                        ->notIn(Post::RESERVED_SLUGS)
                        ->placeholder(__('Auto-generated from title')),

                    RichEditor::make('content')
                        ->hiddenLabel()
                        ->required(),

                    Section::make(__('Excerpt'))
                        ->description(__('Shown on post cards and as the search engine description.'))
                        ->schema([
                            Textarea::make('excerpt')
                                ->hiddenLabel()
                                ->nullable()
                                ->rows(3)
                                ->maxLength(500),
                        ]),
                ])->columnSpan(['lg' => 2]),

                Group::make([
                    Section::make(__('Publish'))
                        ->schema([
                            Select::make('status')
                                ->options(PostStatus::class)
                                ->default(PostStatus::Draft)
                                ->required()
                                ->native(false),

                            DateTimePicker::make('published_at')
                                ->label(__('Publish date'))
                                ->helperText(__('Leave empty to publish immediately. A future date schedules the post.'))
                                ->nullable(),

                            Select::make('author_id')
                                ->relationship('author', 'name')
                                ->label(__('Author'))
                                ->placeholder(__('You'))
                                ->searchable()
                                ->preload()
                                ->nullable(),
                        ]),

                    Section::make(__('Category'))
                        ->schema([
                            Select::make('category_id')
                                ->hiddenLabel()
                                ->relationship('category', 'name')
                                ->createOptionForm(fn (Schema $schema): Schema => CategoryForm::configure($schema))
                                ->searchable()
                                ->preload()
                                ->nullable(),
                        ]),

                    Section::make(__('Tags'))
                        ->schema([
                            Select::make('tags')
                                ->hiddenLabel()
                                ->relationship('tags', 'name')
                                ->multiple()
                                ->createOptionForm(fn (Schema $schema): Schema => TagForm::configure($schema))
                                ->searchable()
                                ->preload(),
                        ]),

                    Section::make(__('Featured image'))
                        ->schema([
                            SpatieMediaLibraryFileUpload::make('cover')
                                ->hiddenLabel()
                                ->collection('cover')
                                ->disk('public')
                                ->visibility('public')
                                ->image()
                                ->imageEditor(),
                        ]),
                ])->columnSpan(['lg' => 1]),
            ]);
    }
}
