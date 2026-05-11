<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    @include ('partials.head')
</head>
<body class="min-h-screen bg-white antialiased dark:bg-linear-to-b dark:from-neutral-950 dark:to-neutral-900">
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
    <div
        class="relative flex min-h-screen flex-col bg-linear-to-r from-emerald-400/10 via-green-200/10 to-teal-400/10 selection:bg-[#FF2D20] selection:text-white dark:from-emerald-900/80 dark:via-green-800/90 dark:to-teal-800/90 dark:text-slate-200"
    >
        <div
            class="relative w-full bg-linear-to-r from-emerald-400/10 via-green-200/10 to-teal-400/10 px-2 selection:bg-[#FF2D20] selection:text-white md:px-6 dark:from-emerald-900/80 dark:via-green-800/90 dark:to-teal-800/90 dark:text-slate-200"
        >
            <x-flash.session />
            <div class="bg-background flex min-h-svh flex-col items-center justify-center gap-6 p-6 md:p-10">
                <div class="flex w-full max-w-sm flex-col gap-2">
                    <a href="{{ route('home') }}" class="flex flex-col items-center gap-2 font-medium" wire:navigate>
                        <span class="mb-1 flex h-9 w-9 items-center justify-center rounded-md">
                            <x-authentication-card-logo class="size-9 fill-current text-black dark:text-white" />
                        </span>
                        <span class="sr-only">{{ config('app.name', 'Laravel') }}</span>
                    </a>
                    <div class="flex flex-col gap-6">{{ $slot }}</div>
                </div>
            </div>
        </div>
    </div>
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
    @persist ('toast')
        <flux:toast.group>
            <flux:toast />
        </flux:toast.group>
    @endpersist

    @fluxScripts
</body>
</html>
