<?php

namespace App\Filament\Resources\Invitations;

use App\Filament\Resources\Invitations\Pages\CreateInvitation;
use App\Filament\Resources\Invitations\Pages\EditInvitation;
use App\Filament\Resources\Invitations\Pages\ListInvitations;
use App\Filament\Resources\Invitations\Pages\ViewInvitation;
use App\Models\Invitation;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class InvitationResource extends Resource
{
    protected static ?string $model = Invitation::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Schema $schema): Schema
    {
        return $schema

            ->columns(['md' => 2])
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('email')
                    ->email()
                    ->required(),
                MarkdownEditor::make('message')
                    ->label('Personal message to add to invitation email')
                    ->hint('This will be included in the invitation email.')
                    ->required()
                    ->toolbarButtons([
                        'blockquote',
                        'bold',
                        'heading',
                        'italic',
                        'link',

                        'redo',
                        'undo',
                    ])
                    ->columnSpanFull(),
                Select::make('user_id')
                    ->relationship('invited_by', 'name')
                    ->default(auth()->user()->id)
                    ->required(),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Invited At')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Invited As')
                    ->description(fn (Invitation $invitation) => $invitation->email)
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Invited By')
                    ->description(fn (Invitation $invitation) => $invitation->user->email)
                    ->sortable(),
                TextColumn::make('joinedAs.name')
                    ->description(fn (Invitation $invitation) => empty($invitation->joinedAs) ? 'not yet' : $invitation->joinedAs->email)
                    ->sortable(),
                TextColumn::make('joined_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
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
                Action::make('send')
                    ->label('Resend')
                    ->visible(fn (Invitation $invitation) => empty($invitation->joinedAs))
                    ->icon('heroicon-o-envelope')
                    ->action(function (Invitation $invitation) {
                        $invitation->send(shouldQueue: true);
                        Notification::make('send')
                            ->title('Invitation Resent')
                            ->success()
                            ->send();
                    }),
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),

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
            'index' => ListInvitations::route('/'),
            'create' => CreateInvitation::route('/create'),
            'view' => ViewInvitation::route('/{record}'),
            'edit' => EditInvitation::route('/{record}/edit'),
        ];
    }
}
