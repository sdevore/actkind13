<?php

use App\Models\Act;
use App\Models\Appreciate;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Livewire\Component;

new class extends Component {
    public int $limit = 5;

    /** @var array */
    public $appreciations = [];

    public string $classes = 'border-1 rounded border dark:border-slate-800 bg-gradient-to-r from-emerald-100 via-green-100 to-teal-100 dark:from-emerald-900 dark:via-green-900 dark:to-teal-900 p-4 shadow';

    public function mount()
    {
        $this->appreciations = Appreciate::query()
            ->with('act', 'user')
            ->whereHasMorph('appreciable', [Act::class], function (Builder $query) {
                $query->where('user_id', Auth::id());
            })
            ->limit($this->limit)
            ->get();
    }
};
?>

<div {{ $attributes->merge(['class' => $classes]) }}>
    {{-- Knowing others is intelligence; knowing yourself is true wisdom. --}}
    <h3 class="text-lg font-bold text-slate-600 dark:text-slate-300">Appreciate you</h3>
    <ul>
        @forelse ($appreciations as $appreciation)
            <li class="flex items-center">
                <span class="grow">
                    <span>{{ $appreciation->user->name }}</span>
                    <em class="text-sm text-slate-500">
                        {{ $appreciation->appreciable->title }}
                        <small>({{ $appreciation->updated_at->diffForHumans() }})</small>
                    </em>
                </span>

                <x-controls.info-link wire:navigate href="{{ route('acts.show',['act'=>$appreciation->appreciable->id]) }}" class="text-nowrap" title="view act">
                    <x-icon name="heroicon-o-eye" class="h-4 pr-1" />
                    <span class="sr-only">View Act</span>
                </x-controls.info-link>
            </li>
        @empty
            <li class="text-sm text-slate-500 dark:text-slate-200">
                When other people have expressed their appreciation for your posted
                <strong>Acts of Kindness</strong>
                the most recent ones will show up here.
            </li>
        @endforelse
    </ul>
</div>
