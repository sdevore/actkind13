<?php

use App\Models\Act;
use Filament\Notifications\Notification;
use Livewire\Attributes\Validate;
use Livewire\Component;

new class extends Component {


    public Act $act;

    #[Validate('required|min:5|max:255')]
    public string $reason = '';

    public bool $showNames = true;

    public bool $showFlagForm = false;

    public string $classes = 'border-1 rounded border  p-4';


    public function save(): void
    {
        $this->authorize('flag', $this->act);
        $this->act->flag(
            auth()->user(),
            $this->reason
        );
        $this->showFlagForm = false;
    }

    public function toggleFlagForm(): void
    {
        // search act->flags for one made by the current user
        if ($this->act->flags->some(function ($flag) {
            return $flag->user_id === auth()->id();
        })) {
            $this->showFlagForm = false;
            Notification::make()
                ->title('You have already flagged this act')
                ->warning()
                ->send();

            return;
        }
        $this->showFlagForm = ! $this->showFlagForm;
    }
};
?>

<div {{ $attributes->merge(['class'=> $classes]) }}>
    <span class="text-danger-600 flex items-center">
        <button wire:click="toggleFlagForm" class="btn btn-sm">
            <x-icon name="fas-flag" class="mr-2 h-4 w-4" />
        </button>
        {{ $act->flags->count() > 0 ? $act->flags->count() : '' }}
        @if ($showFlagForm)
            <div wire:transition>
                <form wire:submit="save">
                    <label for="flag-reason">Flag</label>
                    <input id="flag-reason" type="text" wire:model="reason" />

                    <x-controls.primary-button type="save">Flag Act</x-controls.primary-button>
                </form>
            </div>
        @endif

        @if ($showNames)
            <span class="ml-2 flex items-center space-x-1">
                @foreach ($act->flags as $flag)
                    <span class="flex items-center">
                        <img class="h-6 w-6 rounded-full object-cover" src="{{ $flag->user->profile_photo_url }}" alt="{{ $flag->user->name }}" />
                        {{ $flag->user->name }}
                    </span>
                @endforeach
            </span>
        @endif
    </span>
</div>
