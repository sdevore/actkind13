<?php

use App\Models\Act;
use Livewire\Component;

new class extends Component {
    public Act $act;

    public bool $showNames = false;

    public function appreciate(): void
    {
        $this->act->appreciate(auth()->user());

        $this->act->refresh();
    }

};
?>

<div>
    <span class="{{ $act->type->getTextColor() }} flex items-center">
        <button wire:click="appreciate" class="btn btn-sm">
            <x-icon name="fas-hand-heart" class="mr-2 h-4 w-4" />
        </button>
        {{ $act->appreciates->count() > 0 ? $act->appreciates->count() : '' }}
        @if ($showNames)
            <span class="ml-2 flex items-center space-x-1">
                @foreach ($act->appreciates as $appreciate)
                    <span class="flex items-center">
                        <img class="h-6 w-6 rounded-full object-cover" src="{{ $appreciate->user->profile_photo_url }}" alt="{{ $appreciate->user->name }}" />
                        {{ $appreciate->user->name }}
                    </span>
                @endforeach
            </span>
        @endif
    </span>
</div>
