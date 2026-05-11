{{--
    @extends('layouts.app')

    @section('content')
    invitation.show template
    @endsection
--}}

<x-app-layout>
    <x-common.card>
        <x-slot name="heading">
            <h3 class="text-lg">Invitation to {{ $invitation->name }} @ {{ $invitation->email }}</h3>
        </x-slot>
        <div class="mb-4 p-4">
            <p>Invited by: {{ $invitation->user->name }}</p>
            <p>Invited at: {{ $invitation->created_at->diffForHumans() }}</p>
        </div>
        <div class="mb-4 border border-gray-100 border-gray-200 p-2">
            {!! Str::markdown($invitation->message) !!}
        </div>
        <x-slot name="footer" class="flex justify-between">
            <a
                class="'inline-flex duration-150' items-center rounded-md border border-sky-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-sky-700 shadow-sm transition ease-in-out hover:bg-sky-50 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2 disabled:opacity-25 dark:border-sky-500 dark:bg-sky-800 dark:text-sky-300 dark:hover:bg-sky-700 dark:focus:ring-offset-sky-800"
                wire:navigate="{{ route('invitations.index') }}"
                href="{{ route('invitations.index') }}"
            >
                Back to Invitations
            </a>
        </x-slot>
    </x-common.card>
</x-app-layout>
