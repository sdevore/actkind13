{{--
    @extends('layouts.app')
    
    @section('content')
    invitation.index template
    @endsection
--}}
<x-app-layout title="Invitations">
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">Invitations</h2>
    </x-slot>

    <section class="border-b border-gray-200 bg-white p-6 lg:p-8 dark:border-gray-700 dark:bg-gray-800 dark:bg-gradient-to-bl dark:from-gray-700/50 dark:via-transparent">
        <h3 class="mb-4 text-center text-lg font-semibold leading-tight text-gray-800 dark:text-gray-200">Create an Invitation</h3>
        <livewire:invitations.create class="mx-auto w-5/6 md:w-3/4 lg:w-1/2" />
    </section>
    <section>
        <livewire:invitations.list-invitations />
    </section>
</x-app-layout>
