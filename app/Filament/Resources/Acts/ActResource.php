<?php

namespace App\Filament\Resources\Acts;

use App\Enums\ActType;
use App\Filament\Resources\Acts\Pages\CreateAct;
use App\Filament\Resources\Acts\Pages\EditAct;
use App\Filament\Resources\Acts\Pages\ListActs;
use App\Filament\Resources\Acts\Pages\ViewAct;
use App\Filament\Resources\Acts\RelationManagers\CommentsRelationManager;
use App\Filament\Resources\Acts\Widgets\AppreciatesStatsWidget;
use App\Models\Act;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;

class ActResource extends Resource
{
    protected static ?string $model = Act::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required(),
                Textarea::make('description')
                    ->required()
                    ->columnSpanFull(),
                Select::make('type')
                    ->options(ActType::class)
                    ->default(ActType::DID)
                    ->required(),
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->words(4)
                    ->searchable(),
                TextColumn::make('type')
                    ->badge()
                    ->searchable(),
                TextColumn::make('user.name')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('appreciates_count')
                    ->label(new HtmlString(Blade::render(' <x-icon name="fas-hand-heart" class="mr-2 h-4 w-4" />')))
                    ->counts('appreciates')
                    ->sortable(),
                TextColumn::make('comments_count')
                    ->label(new HtmlString(Blade::render(' <x-icon name="far-comments" class="mr-2 h-4 w-4" />')))
                    ->counts('comments')
                    ->sortable(),
                TextColumn::make('flags_count')
                    ->label(new HtmlString(Blade::render(' <x-icon name="fas-flag" class="mr-2 h-4 w-4" />')))
                    ->counts('flags')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            CommentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListActs::route('/'),
            'create' => CreateAct::route('/create'),
            'view' => ViewAct::route('/{record}'),
            'edit' => EditAct::route('/{record}/edit'),
        ];
    }

    public static function getWidgets(): array
    {
        return [
            AppreciatesStatsWidget::class,
        ];
    }
}
