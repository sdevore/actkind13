@props ([
    'heading',
    'footer',
])
@php
    $classes = 'shadow-secondary-1 block rounded-lg bg-white dark:bg-slate-800/50 dark:text-slate-100';
    $headingClasses = 'flex items-center rounded-t-lg border-b-2 border-neutral-100 px-6 py-3 dark:border-white/10 dark:text-slate-200';
    $footerClasses = 'text-surface/75 border-t-2 border-neutral-100 px-4 py-2 dark:border-white/10 dark:text-neutral-300';
@endphp

<div {{ $attributes->merge(['class' => $classes]) }}>
    @if (! empty($heading))
        <div {{ $heading->attributes->class([$headingClasses]) }}> {{ $heading }}</div>
    @endif

    {{ $slot }}
    @if (! empty($footer))
        <div {{ $footer->attributes->class([$footerClasses]) }}> {{ $footer }}</div>
    @endif
</div>
