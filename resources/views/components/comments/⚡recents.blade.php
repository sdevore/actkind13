<?php

use App\Models\Comment;
use Livewire\Component;

new class extends Component {
    public $comments = [];
    public $limit = 5;
    public ?array $data = [];

    public ?string $classes = 'border-1 rounded border dark:border-slate-800 bg-gradient-to-r from-emerald-100 via-green-100 to-teal-100 dark:from-emerald-900 dark:via-green-900 dark:to-teal-900 p-4 shadow';

    public function mount(): void
    {
        $this->comments = Comment::query()
            ->with('act')
            ->whereHas('act', function ($query) {
                $query->where('user_id', Auth::id());
            })
            ->limit($this->limit)
            ->get();
    }
};
?>

<div {{ $attributes->merge(['class' => $classes]) }}>
    {{-- The best athlete wants his opponent at his best. --}}
    <h3 class="text-lg font-bold text-slate-600 dark:text-slate-300">Recent Comments</h3>
    <ul>
        @forelse ($comments as $comment)
            <li class="flex items-center">
                <span class="grow">
                    <span>{{ $comment->user->name }}</span>

                    <em class="text-sm text-slate-500">
                        {{ $comment->act->title }}
                        <small>({{ $comment->updated_at->diffForHumans() }})</small>
                    </em>
                </span>

                <x-controls.info-link href="{{ route('acts.show',['act'=>$comment->act->id,'comment'=>$comment->id]) }}" class="text-nowrap" title="view act" wire:navigate>
                    <x-icon name="heroicon-o-eye" class="h-4 pr-1" />
                    <span class="sr-only">View Act</span>
                </x-controls.info-link>
            </li>
        @empty
            <li class="text-sm text-slate-500">
                When other people have commented on your posted
                <strong>Acts of Kindness</strong>
                the most recent ones will show up here.
            </li>
        @endforelse
    </ul>
</div>
