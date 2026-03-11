<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Poll Details
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Review poll information, share the public link, and monitor its current status.
                </p>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('admin.polls.index') }}" class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition" >
                    Back to Polls
                </a>

                <a href="{{ route('admin.polls.edit', $poll) }}" class="inline-flex items-center rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-800 transition" >
                    Edit Poll
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div class="grid gap-6 lg:grid-cols-3">
                <div class="lg:col-span-2 space-y-6">
                    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium {{ $poll->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">
                                {{ $poll->is_active ? 'Active' : 'Inactive' }}
                            </span>

                            <span class="text-xs text-gray-400">
                                Created {{ $poll->created_at->format('M d, Y h:i A') }}
                            </span>
                        </div>

                        <h1 class="mt-4 text-2xl font-bold text-gray-900">
                            {{ $poll->question }}
                        </h1>

                        <div class="mt-6">
                            <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">
                                Poll Options
                            </h3>

                            <div class="mt-4 space-y-3">
                                @foreach ($poll->options as $option)
                                    <div class="rounded-xl border border-gray-200 px-4 py-3">
                                        <div class="flex items-center justify-between gap-4">
                                            <span class="text-sm font-medium text-gray-800">
                                                {{ $option->option_text }}
                                            </span>

                                            <span class="text-xs text-gray-400">
                                                Option {{ $loop->iteration }}
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                        <h3 class="text-lg font-semibold text-gray-900">
                            Public Poll Link
                        </h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Share this link with users so they can access and vote on the poll.
                        </p>

                        <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center">
                            <input type="text" id="public-poll-link" readonly value="{{ route('polls.show', $poll) }}" class="block w-full rounded-xl border-gray-300 bg-gray-50 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900" >

                            <button type="button" id="copy-poll-link-button" class="w-48 inline-flex items-center justify-center rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-800 transition">
                                Copy Link
                            </button>
                        </div>

                        <p id="copy-link-feedback" class="mt-3 text-sm text-green-600 hidden">
                            Poll link copied successfully.
                        </p>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                        <h3 class="text-lg font-semibold text-gray-900">
                            Poll Summary
                        </h3>

                        <dl class="mt-5 space-y-4 text-sm">
                            <div class="flex items-start justify-between gap-4">
                                <dt class="text-gray-500">Owner</dt>
                                <dd class="font-medium text-gray-900">{{ $poll->user->name }}</dd>
                            </div>

                            <div class="flex items-start justify-between gap-4">
                                <dt class="text-gray-500">Total Options</dt>
                                <dd class="font-medium text-gray-900">{{ $poll->options_count }}</dd>
                            </div>

                            <div class="flex items-start justify-between gap-4">
                                <dt class="text-gray-500">Total Votes</dt>
                                <dd class="font-medium text-gray-900">{{ $poll->votes_count }}</dd>
                            </div>

                            <div class="flex items-start justify-between gap-4">
                                <dt class="text-gray-500">UUID</dt>
                                <dd class="font-medium text-gray-900 text-right break-all">{{ $poll->uuid }}</dd>
                            </div>
                        </dl>
                    </div>

                    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                        <h3 class="text-lg font-semibold text-gray-900">
                            Schedule
                        </h3>

                        <dl class="mt-5 space-y-4 text-sm">
                            <div class="flex items-start justify-between gap-4">
                                <dt class="text-gray-500">Start</dt>
                                <dd class="font-medium text-gray-900 text-right">
                                    {{ $poll->starts_at ? $poll->starts_at->format('M d, Y h:i A') : 'Not set' }}
                                </dd>
                            </div>

                            <div class="flex items-start justify-between gap-4">
                                <dt class="text-gray-500">End</dt>
                                <dd class="font-medium text-gray-900 text-right">
                                    {{ $poll->ends_at ? $poll->ends_at->format('M d, Y h:i A') : 'Not set' }}
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const copyButton = document.getElementById('copy-poll-link-button');
        const linkInput = document.getElementById('public-poll-link');
        const feedback = document.getElementById('copy-link-feedback');

        if (!copyButton || !linkInput) {
            return;
        }

        copyButton.addEventListener('click', async function () {
            try {
                await navigator.clipboard.writeText(linkInput.value);
                feedback.classList.remove('hidden');

                setTimeout(() => {
                    feedback.classList.add('hidden');
                }, 2000);
            } catch (error) {
                linkInput.select();
                document.execCommand('copy');
                feedback.classList.remove('hidden');

                setTimeout(() => {
                    feedback.classList.add('hidden');
                }, 2000);
            }
        });
    });
</script>
</x-app-layout>