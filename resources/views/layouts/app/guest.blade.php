<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" >
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <title>{{ $title ?config('app.name', 'ActKind . online') .': '  . $title : config('app.name', 'ActKind . online') }}</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net" />
    <link rel="preload" href="https://fonts.bunny.net/figtree/files/figtree-latin-400-normal.woff2" as="font" type="font/woff2" crossorigin />
    <link rel="preload" href="https://fonts.bunny.net/figtree/files/figtree-latin-500-normal.woff2" as="font" type="font/woff2" crossorigin />
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite (['resources/css/app.css', 'resources/js/app.js'])
    <!-- Styles -->
    @livewireStyles
    @stack ('head_scripts')
</head>
<body class="font-sans antialiased">
@if (isset($header))
    <header {{ $header->attributes->class(['bg-white shadow-xs dark:bg-gray-800']) }}>
        <div class="mx-auto max-w-7xl px-4 py-3 sm:px-2 lg:px-6">
            <div class="flex items-center justify-between">
                <x-application-logo class="block h-10 w-auto fill-current text-slate-700" />
                {{ $header }}
            </div>
        </div>
    </header>
@else
    <header class="bg-linear-to-r from-emerald-400 via-green-200 to-teal-400 shadow-xs">
        <div class="mx-auto max-w-7xl px-4 py-3 sm:px-2 lg:px-6">
            <div class="flex items-center justify-between">
                <x-application-logo class="block h-10 w-auto fill-current text-slate-700" />

                @if (Route::has('login'))
                    <nav class="-mx-3 justify-end sm:flex sm:flex-1">
                        @auth
                            <a
                                href="{{ url('/dashboard') }}"
                                class="rounded-md px-3 py-2 text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-hidden focus-visible:ring-[#FF2D20] dark:text-white dark:hover:text-white/80 dark:focus-visible:ring-white"
                            >
                                Dashboard
                            </a>
                        @else
                            <a
                                href="{{ route('contact-us') }}"
                                class="rounded-md px-3 py-2 text-nowrap text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-hidden focus-visible:ring-[#FF2D20]"
                            >
                                Contact us
                            </a>
                            <a
                                href="{{ route('login') }}"
                                class="rounded-md px-3 py-2 text-nowrap text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-hidden focus-visible:ring-[#FF2D20]"
                            >
                                Log in
                            </a>
                            @if (Route::has('register'))
                                <a
                                    href="{{ route('register') }}"
                                    class="rounded-md px-3 py-2 text-nowrap text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-hidden focus-visible:ring-[#FF2D20]"
                                >
                                    Register
                                </a>
                            @endif
                        @endauth
                    </nav>
                @endif
            </div>
        </div>
    </header>
@endif
<div
    class="relative flex min-h-screen flex-col bg-linear-to-r from-emerald-400/10 via-green-200/10 to-teal-400/10 selection:bg-[#FF2D20] selection:text-white dark:from-emerald-900/80 dark:via-green-800/90 dark:to-teal-800/90 dark:text-slate-200"
>
    <div class="relative w-full px-2 md:px-6">
        <x-flash.session />
        <main class="prose max-w-none p-0">{{ $slot }}</main>
    </div>
</div>
@if (isset($footer))
    <footer {{ $footer->attributes->class(['fixed inset-x-0 bottom-0 border border-t-2 border-neutral-200 p-2 text-center']) }}> {{ $footer }}</footer>
@else
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
        @env(['local', 'staging'])
            <span class="text-sm text-slate-500 dark:text-slate-200">Laravel v{{ Illuminate\Foundation\Application::VERSION }} (PHP v{{ PHP_VERSION }})</span>
        @endenv
    </footer>
@endif
<x-display-breakpoint />
@auth ()
    @livewire ('notifications')
@endauth

@stack ('footer_scripts')
</body>
</html>
