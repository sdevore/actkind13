@php
    $classes = 'flex items-center ' . $act->type->getTextColor();
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    <a href="{{ route('acts.show', ['act' => $act]) }}" class="btn btn-sm" wire:navigate>
        <x-icon name="fas-comments" class="mr-2 h-4 w-4 dark:text-slate-300/50" />
    </a>
    <span> {!! $count > 0 ? $count : '<!-- no-count -->' !!} </span>
</span>
