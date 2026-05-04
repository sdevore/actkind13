@php
    $classes = '';
@endphp

@if ($acts->count() > 0)
    <div {{ $attributes->merge(['class' => $classes]) }}>
        <h2 class="mb-2 mt-2 pt-0 text-center text-2xl font-bold text-slate-600 dark:text-slate-200">Acts of
            kindness</h2>
        <div class="mx-1 grid grid-cols-1 gap-4 sm:mx-2 sm:grid-cols-2 md:grid-cols-3 lg:mx-4 lg:grid-cols-3">
            @foreach ($acts as $act)
                <x-acts.act-card :act="$act" :show-name="$showNames" />
            @endforeach
        </div>

    </div>
@endif
