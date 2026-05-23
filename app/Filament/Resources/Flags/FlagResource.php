<?php

namespace App\Filament\Resources\Flags;

use App\Filament\Resources\Flags\Pages\CreateFlag;
use App\Filament\Resources\Flags\Pages\EditFlag;
use App\Filament\Resources\Flags\Pages\ListFlags;
use App\Filament\Resources\Flags\Pages\ViewFlag;
use App\Models\Act;
use App\Models\Comment;
use App\Models\Flag;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FlagResource extends Resource
{
    protected static ?string $model = Flag::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Textarea::make('reason')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('flaggable_type')
                    ->required(),
                TextInput::make('flaggable_id')
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
                TextColumn::make('flaggable_type')
                    ->label('Flagged Type')
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
                TextColumn::make('flaggedUser.name')
                    ->description(function (Flag $record) {
                        $flaggable = $record->flaggable;
                        if ($flaggable instanceof Act) {
                            return $flaggable->title;
                        }
                        if ($flaggable instanceof Comment) {
                            return $flaggable->body;
                        }

                        return 'unknown';
                    }, position: 'above')
                    ->wrap()
                    ->label('Flagged'),
                TextColumn::make('user.name')
                    ->label('Flag')
                    ->description(function (Flag $record) {
                        return $record->reason;
                    }, position: 'above')->wrap()
                    ->searchable(),

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
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([

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
            'index' => ListFlags::route('/'),
            'create' => CreateFlag::route('/create'),
            'view' => ViewFlag::route('/{record}'),
            'edit' => EditFlag::route('/{record}/edit'),
        ];
    }
}
