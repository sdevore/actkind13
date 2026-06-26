@php
    $classes = '';
@endphp

<article {{ $attributes->merge(['class' => $classes]) }}>
    <x-common.card class="border {{ $act->type->getBorderColor() }}  shadow-xl" :key="$act->id">
        <x-slot name="heading" class="{{ $act->type->getLightBackgroundColor() }} relative isolate">
            <h3 class="flex items-center pt-2 text-xl">
                <x-icon name="{{ $act->type->getIcon() }}" class="mr-2 h-12 w-12 {{ $act->type->getTextColor() }} " />
                <a class="dark:text-slate-300" href="{{ route('acts.show', ['act' => $act]) }}" wire:navigate>
                    <span class="absolute inset-0"></span>
                    {{ $act->title }}
                </a>
            </h3>
            <span class="absolute top-0 right-0 m-2 flex w-auto justify-between">
                @if ($showName)
                    <span class="text-sm text-slate-600 dark:text-slate-400">{{ $act->user->name }}</span>
                @endif

                <span class="pl-2 text-sm text-slate-700 dark:text-slate-400">{{ $act->created_at->diffForHumans() }}</span>
            </span>
        </x-slot>
        @if (!empty($act->description))
            <div class="prose p-4 text-sm dark:text-slate-300">
                <p>{{ $act->description }}</p>
            </div>
        @endif

        @auth ()
            <x-slot name="footer">
                <span class="flex items-center justify-between">
                    <livewire:acts.appreciate :act="$act" />
                    <x-acts.comment-count :act="$act" />
                </span>
            </x-slot>
        @endauth

        @guest ()
            @if ($act->appreciates->count() > 0)
                <x-slot name="footer">
                    <span class="{{ $act->type->getTextColor() }} flex items-center">
                        <x-icon name="fas-hand-heart" class="mr-2 h-4 w-4" />
                        {{ $act->appreciates->count() }}
                    </span>
                </x-slot>
            @endif
        @endguest
    </x-common.card>
</article>
