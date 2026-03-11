<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Polls
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Manage all polls created from your account.
                </p>
            </div>

            <a href="{{ route('admin.polls.create') }}" class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                Create Poll
            </a>
        </div>
    </x-slot>

    <div class="py-6" >
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="rounded-2xl border border-dashed border-gray-300 bg-white p-6 text-center">
                <h3 class="text-lg font-semibold text-gray-900">
                    No polls available yet
                </h3>
                <p class="mt-2 text-sm text-gray-600">
                    Your created polls will appear here once poll creation is implemented.
                </p>
            </div>
        </div>
    </div>
</x-app-layout>