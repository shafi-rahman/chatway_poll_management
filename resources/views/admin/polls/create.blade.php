<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Create Poll
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Create a single-question poll with multiple answer options.
                </p>
            </div>

            <a href="{{ route('admin.polls.index') }}" class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                Back to Polls
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="rounded-2xl border border-gray-100 bg-white shadow-sm">
                <div class="border-b border-gray-100 px-6 py-5">
                    <h3 class="text-lg font-semibold text-gray-900">
                        Poll Details
                    </h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Start by writing the question and adding at least two options.
                    </p>
                </div>

                @include('admin.polls._form')
                
            </div>
        </div>
    </div>
</x-app-layout>
