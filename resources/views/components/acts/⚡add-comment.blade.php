<?php

use App\Models\Comment;
use Livewire\Component;
use Livewire\Attributes\Validate;

new class extends Component {


    public \App\Models\Act $act;

    #[Validate('required|min:5|max:255')]
    public string $body = '';

  public string $classes = 'border-1 rounded border  p-4';

public bool $showForm = false;

    public function mount(): void
    {
    }

    public function save(): void
    {
        $this->authorize('create', Comment::class);
        $comment = $this->act->comment(
            Auth::user(), $this->body
        );

        session()->flash('status', 'Post successfully updated.');
        $this->dispatch('saved', ['comment_id' => $comment->id]);
        $this->body = '';
        $this->showForm = false;
    }

    public function toggleForm(): void
    {
        $this->showForm = !$this->showForm;
    }
};
?>

<div {{ $attributes->merge(['class'=> $classes]) }}>
    {{-- The best athlete wants his opponent at his best. --}}
    @if (! $showForm)
        <div wire:transition>
            <x-controls.primary-button wire:click="toggleForm" class="">Add Comment</x-controls.primary-button>
        </div>
    @endif

    @if ($showForm)
        <div wire:transition>
            <form wire:submit="save">
                <label for="body">Comment</label>
                <input id="comment-body" type="text" wire:model="body" />

                <x-controls.primary-button type="save">Save</x-controls.primary-button>
            </form>
        </div>
    @endif
</div>
