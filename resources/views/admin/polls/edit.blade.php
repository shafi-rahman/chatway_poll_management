<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Edit Poll
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Update your poll question, options, status, and schedule.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.polls.index') }}" class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                    Back to Polls
                </a>

                <a href="{{ route('admin.polls.show', $poll) }}" class="inline-flex items-center rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-800 transition">
                    View Poll
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="rounded-2xl border border-gray-100 bg-white shadow-sm">
                <div class="border-b border-gray-100 px-6 py-5">
                    <h3 class="text-lg font-semibold text-gray-900">
                        Edit Poll Details
                    </h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Options with votes cannot be deleted - you can edit their text or mark them inactive.
                    </p>
                </div>

                @include('admin.polls._form', ['poll' => $poll])
                
            </div>
        </div>
    </div>
</x-app-layout>
