{{--
    @extends('layouts.app')
    
    @section('content')
    invitation.create template
    @endsection
--}}
<x-app-layout title="Create Invitation">
    <x-slot name="header">
        <h2 class="text-xl leading-tight font-semibold text-gray-800">Create Invitation</h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-xl sm:rounded-lg">
                <div class="border-b border-gray-200 bg-white p-6 sm:px-20">
                    <div class="text-2xl">Invite someone to share their Acts of Kindness</div>
                    <form method="POST" action="{{ route('invitations.store') }}">
                        @csrf
                        <div class="my-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <x-label for="name" :value="__('Name')" />

                                <x-input id="name" class="mt-1 block w-full" type="text" name="name" required autofocus />
                            </div>

                            <div>
                                <x-label for="email" :value="__('Email')" />

                                <x-input id="email" class="mt-1 block w-full" type="email" name="email" required />
                            </div>
                        </div>

                        <x-common.simple-markdown-input label="Short text to include in the invitation" name="message" />
                        <div class="mt-4 flex items-center justify-end">
                            <x-button class="ml-4"> {{ __('Send Invitation') }} </x-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
