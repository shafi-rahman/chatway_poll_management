<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Poll Results
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Monitor vote distribution and current response totals for this poll.
                </p>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('admin.polls.show', $poll) }}" class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                    Poll Details
                </a>

                <a href="{{ route('admin.polls.index') }}" class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                    Back to Polls
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6" data-poll-uuid="{{ $poll->uuid }}">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div class="grid gap-6 lg:grid-cols-3">
                <div class="lg:col-span-2 space-y-6">
                    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                        <div class="flex flex-wrap items-center justify-between gap-4">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Question</p>
                                <h1 class="mt-2 text-2xl font-bold text-gray-900">
                                    {{ $poll->question }}
                                </h1>
                            </div>

                            <div class="rounded-2xl border border-gray-100 bg-gray-50 px-5 py-4 text-center">
                                <div class="text-sm text-gray-500">Total Votes</div>
                                <div id="results-total-votes" class="mt-1 text-3xl font-bold text-gray-900">
                                    {{ $totalVotes }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                        <h3 class="text-lg font-semibold text-gray-900">
                            Vote Breakdown
                        </h3>

                        <div id="results-breakdown" class="mt-6 space-y-5">
                            @foreach ($resultRows as $row)
                                <div class="result-row" data-option-id="{{ $row['id'] }}">
                                    <div class="mb-2 flex items-center justify-between gap-4">
                                        <div class="text-sm font-medium text-gray-800">
                                            {{ $row['option_text'] }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            <span class="result-vote-count">{{ $row['vote_count'] }}</span> votes -
                                            <span class="result-percentage">{{ number_format($row['percentage'], 1) }}</span>%
                                        </div>
                                    </div>

                                    <div class="h-3 overflow-hidden rounded-full bg-gray-100">
                                        <div class="result-bar h-full rounded-full bg-gray-900 transition-all duration-300" style="width: {{ $row['percentage'] }}%;"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                        <h3 class="text-lg font-semibold text-gray-900">
                            Poll Summary
                        </h3>

                        <dl class="mt-5 space-y-4 text-sm">
                            <div class="flex items-start justify-between gap-4">
                                <dt class="text-gray-500">Status</dt>
                                <dd class="font-medium text-gray-900">
                                    {{ $poll->is_active ? 'Active' : 'Inactive' }}
                                </dd>
                            </div>

                            <div class="flex items-start justify-between gap-4">
                                <dt class="text-gray-500">Options</dt>
                                <dd class="font-medium text-gray-900">{{ $resultRows->count() }}</dd>
                            </div>

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

                    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                        <h3 class="text-lg font-semibold text-gray-900">
                            Public Link
                        </h3>

                        <div class="mt-4">
                            <input type="text" readonly value="{{ route('polls.show', $poll) }}" class="block w-full rounded-xl border-gray-300 bg-gray-50 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const container = document.querySelector('[data-poll-uuid]');
        if (!container || typeof window.Echo === 'undefined') {
            return;
        }

        const pollUuid = container.dataset.pollUuid;
        const totalVotesElement = document.getElementById('results-total-votes');

        window.Echo.channel(`poll.${pollUuid}`)
            .listen('.poll.vote.updated', (event) => {
                if (totalVotesElement) {
                    totalVotesElement.textContent = event.total_votes;
                }

                (event.result_rows || []).forEach((row) => {
                    const rowElement = document.querySelector(`.result-row[data-option-id="${row.id}"]`);
                    if (!rowElement) {
                        return;
                    }

                    const voteCount = rowElement.querySelector('.result-vote-count');
                    const percentage = rowElement.querySelector('.result-percentage');
                    const bar = rowElement.querySelector('.result-bar');

                    if (voteCount) {
                        voteCount.textContent = row.vote_count;
                    }

                    if (percentage) {
                        percentage.textContent = Number(row.percentage).toFixed(1);
                    }

                    if (bar) {
                        bar.style.width = `${row.percentage}%`;
                    }
                });
            });
    });
</script>
</x-app-layout>