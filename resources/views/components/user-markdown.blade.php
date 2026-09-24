@props(['markdown'])

{{-- User-written markdown: raw HTML is escaped and javascript:/data: links are dropped, so it cannot inject script. --}}
{!! Str::markdown((string) $markdown, ['html_input' => 'escape', 'allow_unsafe_links' => false]) !!}
