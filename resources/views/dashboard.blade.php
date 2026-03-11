<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Admin Dashboard') }}
            </h2>
            <p class="text-sm text-gray-500">
                Manage your polls, track responses, and monitor live results.
            </p>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="mt-6 mb-6 bg-white overflow-hidden shadow-sm rounded-2xl border border-gray-100 p-6">
                <div class="text-sm font-medium text-gray-500">Welcome</div>
                <div class="mt-2 text-xl font-semibold text-gray-900">
                    {{ auth()->user()->name }}
                </div>
                <div class="mt-1 text-sm text-gray-600">
                    You are logged in as an {{ auth()->user()->role_label }}.
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm rounded-2xl border border-gray-100 p-6">
                <div class="text-sm font-medium text-gray-500">Polls</div>
                <div class="mt-2 text-3xl font-bold text-gray-900">0</div>
                <div class="mt-1 text-sm text-gray-600">
                    Create and manage polls from your admin panel.
                </div>
                <div class="mt-4">
                    <a href="{{ route('admin.polls.index') }}" class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition" >
                        View Polls
                    </a>
                </div>
            </div>


        </div>
    </div>
</x-app-layout>