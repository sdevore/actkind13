<x-layouts::app :title="$act->title">
    @ray($act)
    <h2 class="w-full  mt-4 flex justify-around pb-2 text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200/80">
        <a class="dark:text-slate-200/80 dark:hover:text-gray-700"
           href="{{ route('acts.index') }}">{{ __('Act of Kindness') }}</a>
        <span >

             {{ $act->title }}
        </span>
    </h2>

    <div class="py-4">
        <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
            <div class="flex items-center justify-between py-2 dark:text-slate-200">
                <span class="flex">
                    <x-icon class="h-6 px-2" name="{{$act->type->getIcon()}}" />
                    {!! $act->type->getBadge() !!}
                </span>
                @auth()
                    <span>
                        By:
                        <strong>{{ $act->user->name }}</strong>
                    </span>
                @endauth

                <span class="flex items-center">
                    <x-icon class="h-4 pr-2" name="fas-calendar-day" />
                    {{ $act->created_at->diffForHumans() }}
                </span>
            </div>
            <div class="bg-white shadow-lg sm:rounded-lg dark:bg-gray-800/30 text-slate-800 dark:text-slate-200">
                <div class="prose p-6 dark:text-slate-200">
                    {!! Str::markdown($act->description) !!}
                </div>
            </div>
            <div class="mt-4">
                @auth()
                    <livewire:acts.appreciate :act="$act" :show-names="true" />
                @endauth

                @guest()
                    @if ($act->appreciates->count() > 0)
                        <span class="{{ $act->type->getTextColor() }} flex items-center">
                            <x-icon name="fas-hand-heart" class="mr-2 h-4 w-4" />
                            {{ $act->appreciates->count() }}
                        </span>
                    @endif
                @endguest
            </div>
            @can('view flags')
                <div class="mt-4">
                    <livewire:acts.flag :act="$act" />
                </div>
            @endcan

            <div>
                <livewire:comments.comment-list :act="$act" />
            </div>
        </div>
    </div>
</x-layouts::app>
