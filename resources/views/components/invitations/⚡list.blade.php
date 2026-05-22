<?php

use App\Models\Invitation;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Filters\TernaryFilter;
use Livewire\Component;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

new class extends Component implements HasActions, HasSchemas, HasTable {
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;


    public function table(Table $table): Table
    {
        return $table
            ->query(Invitation::query()
                ->where('user_id', auth()->id()))
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')
                    ->label('Invited At')
                    ->since()->grow(false)
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Name')
                    ->description(fn(Invitation $invitation) => $invitation->email)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('joined_at')
                    ->label('Joined')
                    ->datetime()
                    ->grow(false)
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('Joined')
                    ->nullable()
                    ->default(false)
                    ->trueLabel('Yes')
                    ->falseLabel('Not Yet')
                    ->attribute('joined_at'),
            ])
            ->actions([

                Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->button()
                    ->color('info')
                    ->url(function (Invitation $invitation) {
                        return route('invitations.show', $invitation);
                    }),
                Action::make('resend')
                    ->label('Resend')
                    ->hidden(fn(Invitation $invitation) => $invitation->joined !== null)
                    ->icon('heroicon-o-paper-airplane')
                    ->requiresConfirmation()
                    ->button()
                    ->color('primary')
                    ->action(function (Invitation $invitation) {
                        $invitation->send(shouldQueue: false);
                        Notification::make()
                            ->title('Resent Invitation to ' . $invitation->name)
                            ->success()
                            ->send();
                    }),
                Action::make('delete')
                    ->label('Delete')
                    ->hidden(fn(Invitation $invitation) => $invitation->joined !== null)
                    ->icon('heroicon-o-trash')
                    ->requiresConfirmation()
                    ->button()
                    ->color('danger')
                    ->action(function (Invitation $invitation) {
                        $invitation->delete();
                        Notification::make()
                            ->title('Deleted Invitation to ' . $invitation->name)
                            ->danger()
                            ->send();
                    }),
            ]);
    }
};
?>

<div>{{ $this->table }}</div>
