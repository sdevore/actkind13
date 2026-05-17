<?php

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Livewire\Component;
use App\Enums\ActType;
use App\Models\Act;
use Filament\Forms;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Notifications\Notification;
use Illuminate\Contracts\View\View;
use Filament\Schemas\Schema;

new class extends Component implements HasSchemas {
    use InteractsWithSchemas;

    public ?array $data = [];

    #[Session]
    public bool $showHelp = false;

    public $class = '';

    public ?string $mergedClasses = 'border-1 rounded border dark:border-slate-800 bg-slate-100 dark:bg-slate-900 p-4 shadow';

    public function mount(): void
    {
        $this->form->fill();
        $split = array_merge(explode(' ', $this->class), explode(' ', $this->mergedClasses));
        $this->mergedClasses = implode(' ', array_unique($split));
    }

    public function toggleHelp(): void
    {
        $this->showHelp = !$this->showHelp;

    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label('The Act of Kindness')
                    ->helperText(
                        'This is the content above the field\'s error message'
                    )
                    ->required(),
                Select::make('type')
                    ->label('Type of this act?')
                    ->helperText('Did you do, see, or receive this kindness?')
                    ->default(ActType::DID)
                    ->options(ActType::class)
                    ->required(),
                Textarea::make('description')
                    ->columnSpan(['md' => 2])
                    ->label('Describe the Act of Kindness')
                    ->helperText('If the title is not enough, please provide more details')
                    ->required(false)

            ])
            ->statePath('data')
            ->model(Act::class);
    }

    public function create(): void
    {
        $data = $this->form->getState();
        $data['user_id'] = auth()->id();
        if (empty($data['description'])) {
            $data['description'] = '';
        }
        $record = Act::create($data);

        $this->form->model($record)->saveRelationships();
        $this->form->fill();
        Notification::make()
            ->title($data['title'] . ' Saved successfully')
            ->success()
            ->send();
    }

};
?>

<div class="{{ $mergedClasses }}">
    <form wire:submit="create">
        {{ $this->form }}

        <x-controls.primary-button type="submit" class="mt-4">Share</x-controls.primary-button>
        <x-controls.info-button wire:click="toggleHelp" class="float-end mt-4 text-sm">
            <x-icon name="heroicon-c-question-mark-circle" class="mr-2 h-4" />
            {{ $showHelp ? 'Hide Help' : 'Show Help' }}
        </x-controls.info-button>
    </form>
    @if ($showHelp)
        <div
            wire:transition
            class="mx-auto mt-4 max-w-lg items-center rounded-lg border bg-green-200/20 p-4 text-sm text-green-700 shadow dark:border-green-600 dark:bg-slate-500/50 dark:text-green-300"
        >
            <span class="font-bold">Some notes about sharing an act of kindness:</span>
            <ul class="list-inside list-disc">
                <li>Your name will only be shown to members that are signed in</li>
                <li>Comments are only shown to signed in members</li>
                <li class="italic">While we are testing you or I can always delete any experiments before things open up</li>
            </ul>
        </div>
    @endif
    <x-filament-actions::modals />
</div>
