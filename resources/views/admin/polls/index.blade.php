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

            <a
                href="{{ route('admin.polls.create') }}"
                class="inline-flex items-center rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-800 transition"
            >
                Create Poll
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-6 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    {{ session('error') }}
                </div>
            @endif

            @if ($polls->count() === 0)
                <div class="rounded-2xl border border-dashed border-gray-300 bg-white p-10 text-center">
                    <h3 class="text-lg font-semibold text-gray-900">
                        No polls available yet
                    </h3>
                    <p class="mt-2 text-sm text-gray-600">
                        Create your first poll to start collecting responses.
                    </p>

                    <div class="mt-6">
                        <a
                            href="{{ route('admin.polls.create') }}"
                            class="inline-flex items-center rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-800 transition"
                        >
                            Create Your First Poll
                        </a>
                    </div>
                </div>
            @else
                <div class="grid gap-4">
                    @foreach ($polls as $poll)
                        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                            <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium {{ $poll->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">
                                            {{ $poll->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                        <span class="text-xs text-gray-400">
                                            Created {{ $poll->created_at->diffForHumans() }}
                                        </span>
                                    </div>

                                    <h3 class="mt-3 text-lg font-semibold text-gray-900">
                                        {{ $poll->question }}
                                    </h3>

                                    <div class="mt-3 flex flex-wrap gap-4 text-sm text-gray-600">
                                        <span>{{ $poll->options_count }} options</span>
                                        <span>{{ $poll->votes_count }} votes</span>
                                        <span>UUID: {{ $poll->uuid }}</span>
                                    </div>

                                    <div class="mt-3 flex flex-col gap-1 text-sm text-gray-500">
                                        <span>
                                            Start:
                                            {{ $poll->starts_at ? $poll->starts_at->format('M d, Y h:i A') : 'Not set' }}
                                        </span>
                                        <span>
                                            End:
                                            {{ $poll->ends_at ? $poll->ends_at->format('M d, Y h:i A') : 'Not set' }}
                                        </span>
                                    </div>
                                </div>

                                <div class="flex items-center gap-3">
                                    <a
                                        href="{{ route('admin.polls.edit', $poll) }}"
                                        class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition"
                                    >
                                        Edit
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $polls->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>