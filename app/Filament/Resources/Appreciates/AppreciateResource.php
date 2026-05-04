<?php

namespace App\Filament\Resources\Appreciates;

use App\Filament\Resources\Appreciates\Pages\CreateAppreciate;
use App\Filament\Resources\Appreciates\Pages\EditAppreciate;
use App\Filament\Resources\Appreciates\Pages\ListAppreciates;
use App\Filament\Resources\Appreciates\Pages\ViewAppreciate;
use App\Models\Act;
use App\Models\Appreciate;
use App\Models\Comment;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AppreciateResource extends Resource
{
    protected static ?string $model = Appreciate::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('appreciable_type')
                    ->required(),
                TextInput::make('appreciable_id')
                    ->required()
                    ->numeric(),
                TextInput::make('user_id')
                    ->required()
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('appreciable_type')
                    ->label('Type')
                    ->formatStateUsing(function ($state) {
                        return match ($state) {
                            Act::class => 'Act',
                            Comment::class => 'Comment',
                            default => $state,
                        };
                    })
                    ->badge()
                    ->color(function ($state) {
                        return match ($state) {
                            Act::class => 'primary',
                            Comment::class => 'info',
                            default => 'secondary',
                        };
                    })
                    ->searchable(),
                TextColumn::make('act')
                    ->getStateUsing(function (Appreciate $record) {
                        return match ($record->appreciable_type) {
                            Act::class => $record->act->title,
                            Comment::class => $record->comment->content,
                            default => 'unknown',
                        };
                    })->wrap(),
                TextColumn::make('user.name')
                    ->label('By')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAppreciates::route('/'),
            'create' => CreateAppreciate::route('/create'),
            'view' => ViewAppreciate::route('/{record}'),
            'edit' => EditAppreciate::route('/{record}/edit'),
        ];
    }
}
