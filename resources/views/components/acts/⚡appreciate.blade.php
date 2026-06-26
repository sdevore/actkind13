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
            <span class="ml-2 flex items-center space-x-1 divide-x divide-solid divide-gray-300">
                @foreach ($act->appreciates as $appreciate)
                    <span class="flex-auto items-center px-2 text-sm text-gray-600 dark:text-gray-400"> {{ $appreciate->user->name }} </span>
                @endforeach
            </span>
        @endif
    </span>
</div>
