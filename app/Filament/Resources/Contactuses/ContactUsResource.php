<?php

namespace App\Filament\Resources\Contactuses;

use App\Filament\Resources\Contactuses\Pages\CreateContactUs;
use App\Filament\Resources\Contactuses\Pages\EditContactUs;
use App\Filament\Resources\Contactuses\Pages\ListContactUs;
use App\Models\ContactUs;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ContactUsResource extends Resource
{
    protected static ?string $model = ContactUs::class;

    protected static string $title = 'Contacted Us';

    protected static ?string $pluralModelLabel = 'Contacted Us';

    protected static ?string $navigationLabel = 'Contacted Us';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('email')
                    ->email()
                    ->required(),
                TextInput::make('where_from')
                    ->required(),
                TextInput::make('invitation_id')
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('name')
                    ->description(fn (ContactUs $contactUs) => $contactUs->email)
                    ->searchable(),
                TextColumn::make('where_from')
                    ->searchable(),
                TextColumn::make('invitation.created_at')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
                ViewAction::make(),
                DeleteAction::make(),
                Action::make('Invite')
                    ->requiresConfirmation('Are you sure you want to send an invitation?')
                    ->visible(fn (ContactUs $contactUs) => empty($contactUs->invitation_id))
                    ->icon('heroicon-o-envelope-open')
                    ->color('success')
                    ->action(function (ContactUs $contactUs) {
                        $contactUs->sendInvitation();
                        Notification::make()
                            ->title($contactUs->name.' Invited')
                            ->body($contactUs->name.'has had an invitation sent to'.$contactUs->email)
                            ->icon('heroicon-o-envelope')
                            ->iconColor('success')
                            ->color('success')
                            ->send();
                    }),
                Action::make('Re-invite')
                    ->label('Re-invite')
                    ->visible(fn (ContactUs $contactUs) => ! empty($contactUs->invitation_id))
                    ->requiresConfirmation('Are you sure you want to send an invitation?')
                    ->icon('heroicon-o-envelope-open')
                    ->color('info')
                    ->action(function (ContactUs $contactUs) {
                        $contactUs->resendInvitation();
                        Notification::make()
                            ->title($contactUs->name.' Invited')
                            ->body($contactUs->name.'has had an invitation re-sent to'.$contactUs->email)
                            ->icon('heroicon-o-envelope')
                            ->iconColor('success')
                            ->color('success')
                            ->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
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
            'index' => ListContactUs::route('/'),
            'create' => CreateContactUs::route('/create'),
            'edit' => EditContactUs::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
