<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    @include ('partials.head')
    {{ filament()->getTheme()->getHtml() }} {{ filament()->getFontHtml() }}
    @filamentStyles
</head>
<body
    class="min-h-screen bg-gradient-to-r from-emerald-400/10 via-green-200/10 to-teal-400/10 font-sans antialiased selection:bg-[#FF2D20] selection:text-white dark:from-emerald-900/80 dark:via-green-800/90 dark:to-teal-800/90 dark:text-slate-200"
>
    <flux:header container class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
        <flux:sidebar.toggle class="mr-2 lg:hidden" icon="bars-2" inset="left" />

        <x-app-logo href="{{ route('dashboard') }}" wire:navigate />

        <flux:navbar class="-mb-px max-lg:hidden">
            <flux:navbar.item icon="layout-grid" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate> {{ __('Dashboard') }} </flux:navbar.item>
            @can ('invite users')
                <flux:navbar.item icon="user-plus" :href="route('invitations.index')" :current="request()->routeIs('invitations.index')" wire:navigate>
                    {{ __('Invitations') }}
                </flux:navbar.item>
            @endcan
            <flux:navbar.item icon="hand-heart" :href="route('acts.mine')" :current="request()->routeIs('acts.mine')" wire:navigate>
                {{ __('Acts of your kindness') }}
            </flux:navbar.item>
            @can ('view admin panel')
                <flux:navbar.item icon="shield-check" :href="route('filament.admin.pages.dashboard')" :current="request()->routeIs('filament.admin.pages.dashboard')" wire:navigate>
                    {{ __('Admin Panel') }}
                </flux:navbar.item>
            @endcan
        </flux:navbar>

        <flux:spacer />

        <flux:navbar class="me-1.5 space-x-0.5 py-0! rtl:space-x-reverse">
            <flux:tooltip :content="__('Search')" position="bottom">
                <flux:navbar.item class="[&>div>svg]:size-5 !h-10" icon="magnifying-glass" href="#" :label="__('Search')" />
            </flux:tooltip>
        </flux:navbar>

        <x-desktop-user-menu />
    </flux:header>

    <!-- Mobile Menu -->
    <flux:sidebar collapsible="mobile" sticky class="border-e border-zinc-200 bg-zinc-50 lg:hidden dark:border-zinc-700 dark:bg-zinc-900">
        <flux:sidebar.header>
            <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
            <flux:sidebar.collapse class="in-data-flux-sidebar-on-desktop:not-in-data-flux-sidebar-collapsed-desktop:-mr-2" />
        </flux:sidebar.header>

        <flux:sidebar.nav>
            <flux:sidebar.group :heading="__('Platform')">
                <flux:sidebar.item icon="layout-grid" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                    {{ __('Dashboard')  }}
                </flux:sidebar.item>
            </flux:sidebar.group>
        </flux:sidebar.nav>

        <flux:spacer />

        <flux:sidebar.nav>
            <flux:sidebar.item icon="folder-git-2" href="https://github.com/laravel/livewire-starter-kit" target="_blank"> {{ __('Repository') }} </flux:sidebar.item>
            <flux:sidebar.item icon="book-open-text" href="https://laravel.com/docs/starter-kits#livewire" target="_blank"> {{ __('Documentation') }} </flux:sidebar.item>
        </flux:sidebar.nav>
    </flux:sidebar>

    {{ $slot }}
    <footer
        class="fixed inset-x-0 bottom-0 flex items-center justify-between border border-t-2 border-neutral-200 bg-linear-to-r from-emerald-400/20 via-green-200/20 to-teal-400/20 p-2 text-center dark:border-neutral-500/50"
    >
        <span class="text-sm text-slate-500 dark:text-slate-200">&copy; {{ date('Y') }} ActKind . online</span>
        <span class="mx-2 text-slate-700">
            <a
                href="{{ route('terms') }}"
                class="p-2 pr-1 text-sm text-slate-500 hover:rounded-md hover:border hover:border-gray-300 hover:border-gray-300/20 hover:bg-green-600/20 dark:text-slate-200"
            >
                Terms, Conditions and Community Guidelines
            </a>
            &bull;
            <a
                href="{{ route('policy') }}"
                class="p-2 pl-1 text-sm text-slate-500 hover:rounded-md hover:border hover:border-gray-300 hover:border-gray-300/20 hover:bg-green-600/20 dark:text-slate-200"
            >
                Privacy Policy
            </a>
        </span>
        @env (['local', 'staging'])
            <span class="text-sm text-slate-500 dark:text-slate-200">Laravel v{{ Illuminate\Foundation\Application::VERSION }} (PHP v{{ PHP_VERSION }})</span>
        @endenv
    </footer>
    <x-display-breakpoint />
    @persist ('toast')
        <flux:toast.group>
            <flux:toast />
        </flux:toast.group>
    @endpersist
    @auth ()
        @livewire ('notifications')
    @endauth
    @fluxScripts
    @filamentScripts
    @livewireScripts
    @stack ('footer_scripts')
</body>
</html>
