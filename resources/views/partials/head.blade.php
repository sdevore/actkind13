<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />

<title>{{ filled($title ?? null) ? config('app.name', 'ActKind . online') . ': ' . $title : config('app.name', 'ActKind . online') }}</title>

<link rel="icon" href="/favicon.ico" sizes="any" />
<link rel="icon" href="/favicon.svg" type="image/svg+xml" />
<link rel="apple-touch-icon" href="/apple-touch-icon.png" />

@fonts

@vite (['resources/css/app.css', 'resources/js/app.js'])

<!-- Styles -->
@fluxAppearance
@livewireStyles

<!-- Filament Styles --> {{ filament()->getTheme()->getHtml() }} {{ filament()->getFontHtml() }}
@filamentStyles

<!-- turnstile -->
<x-turnstile.scripts />
@stack ('head_scripts')
