<?php

use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use App\Models\Invitation;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

new class extends Component implements HasSchemas {
    use InteractsWithSchemas;

    public ?array $data = [];

    public string $classes = 'border-1 rounded border dark:border-slate-800 bg-slate-100 dark:bg-slate-900 p-4 shadow';

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->required(),
                TextInput::make('email')->email()->required(),
                MarkdownEditor::make('message')
                    ->label('Personal message')
                    ->hint('This will be included in the invitation email.')
                    ->required()
                    ->toolbarButtons(['blockquote', 'bold', 'heading', 'italic', 'link', 'redo', 'undo'])
                    ->columnSpanFull(),
            ])
            ->statePath('data')
            ->model(Invitation::class);
    }

    public function create(): void
    {
        $data = $this->form->getState();

        $record = Invitation::make($data);

        Auth::user()->sendInvitation($record);
        $this->reset();
        // todo
        // $this->dispatch('invitation-created')->to(ListInvitations::class);
        Notification::make()
            ->title("Invitation sent to {$record->name}")
            ->body("An invitation has been sent to {$record->name} at {$record->email}.")
            ->success()
            ->send();
    }
};
?>

<div {{ $attributes->merge(['class' => $classes]) }}>
    <p class="mb-2 rounded border border-green-900 bg-green-200/80 p-2 font-bold text-green-700 shadow-sm">Be thoughtful about who you invite we are trying to make this a thoughtful kind community. Invitations are sent via email.</p>
    <form wire:submit="create">
        {{ $this->form }}
        <x-controls.primary-button type="submit" class="mt-4">Invite</x-controls.primary-button>
    </form>

    <x-filament-actions::modals />
</div>
