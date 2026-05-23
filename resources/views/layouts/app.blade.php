@guest
    <x-layouts::app.guest :title="$title ?? null"> {{ $slot }} </x-layouts::app.guest>
@endguest
@auth
    <x-layouts::app.header :title="$title ?? null">
        <flux:main> {{ $slot }} </flux:main>
    </x-layouts::app.header>
@endauth
