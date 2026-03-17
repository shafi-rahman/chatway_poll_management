<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Poll Details
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Monitor results, voter activity, and manage your poll.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.polls.index') }}" class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                    Back to Polls
                </a>

                <a href="{{ route('admin.polls.edit', $poll) }}" class="inline-flex items-center rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-800 transition">
                    Edit Poll
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6" data-poll-uuid="{{ $poll->uuid }}">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Question + Total Votes --}}
            <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap items-center gap-2 mb-3">
                            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium {{ $poll->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">
                                {{ $poll->is_active ? 'Active' : 'Inactive' }}
                            </span>
                            <span class="text-xs text-gray-400">
                                Created {{ $poll->created_at->format('M d, Y h:i A') }}
                            </span>
                        </div>

                        <h1 class="text-2xl font-bold text-gray-900">
                            {{ $poll->question }}
                        </h1>
                    </div>

                    <div class="rounded-2xl border border-gray-100 bg-gray-50 px-5 py-4 text-center shrink-0">
                        <div class="text-sm text-gray-500">Total Votes</div>
                        <div id="results-total-votes" class="mt-1 text-3xl font-bold text-gray-900">
                            {{ $totalVotes }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <div class="lg:col-span-2 space-y-6">

                    {{-- Vote Breakdown --}}
                    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                        <h3 class="text-lg font-semibold text-gray-900">
                            Vote Breakdown
                        </h3>

                        <div id="results-breakdown" class="mt-6 space-y-5">
                            @foreach ($resultRows as $row)
                                <div class="result-row" data-option-id="{{ $row['id'] }}">
                                    <div class="mb-2 flex items-center justify-between gap-4">
                                        <div class="flex items-center gap-2 min-w-0">
                                            <span class="text-sm font-medium text-gray-800 truncate">
                                                {{ $row['option_text'] }}
                                            </span>
                                            @if (!$row['is_active'])
                                                <span class="shrink-0 inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-xs text-gray-500">
                                                    Inactive
                                                </span>
                                            @endif
                                        </div>
                                        <div class="text-sm text-gray-500 shrink-0">
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

                    {{-- Voter Activity History --}}
                    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                        <h3 class="text-lg font-semibold text-gray-900">
                            Voter Activity
                        </h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Latest {{ $voteHistory->count() }} actions - initial votes and changes.
                        </p>

                        @if ($voteHistory->isEmpty())
                            <p class="mt-6 text-center text-sm text-gray-400">No votes have been cast yet.</p>
                        @else
                            <div class="mt-5 overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead>
                                        <tr class="border-b border-gray-100 text-left text-xs font-medium uppercase tracking-wide text-gray-400">
                                            <th class="pb-3 pr-4">Time</th>
                                            <th class="pb-3 pr-4">Voter</th>
                                            <th class="pb-3">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-50">
                                        @foreach ($voteHistory as $entry)
                                            <tr class="py-3">
                                                <td class="py-3 pr-4 text-gray-400 whitespace-nowrap">
                                                    {{ $entry->created_at->format('M d, H:i') }}
                                                </td>
                                                <td class="py-3 pr-4 font-mono text-xs text-gray-500 whitespace-nowrap">
                                                    {{ substr($entry->session_token, 0, 8) }}…
                                                </td>
                                                <td class="py-3 text-gray-700">
                                                    @if ($entry->from_option_id === null)
                                                        Voted for
                                                        <span class="font-medium text-gray-900">{{ $entry->toOption->option_text ?? '-' }}</span>
                                                    @else
                                                        Changed from
                                                        <span class="font-medium text-gray-900">{{ $entry->fromOption->option_text ?? '-' }}</span>
                                                        to
                                                        <span class="font-medium text-gray-900">{{ $entry->toOption->option_text ?? '-' }}</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Sidebar --}}
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
                                <dt class="text-gray-500">Status</dt>
                                <dd class="font-medium text-gray-900">{{ $poll->is_active ? 'Active' : 'Inactive' }}</dd>
                            </div>

                            <div class="flex items-start justify-between gap-4">
                                <dt class="text-gray-500">Options</dt>
                                <dd class="font-medium text-gray-900">{{ $poll->options_count }}</dd>
                            </div>

                            <div class="flex items-start justify-between gap-4">
                                <dt class="text-gray-500">UUID</dt>
                                <dd class="font-medium text-gray-900 text-right break-all text-xs">{{ $poll->uuid }}</dd>
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

                    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                        <h3 class="text-lg font-semibold text-gray-900">
                            Public Link
                        </h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Share with voters.
                        </p>

                        <div class="mt-4 space-y-3">
                            <input type="text" id="public-poll-link" readonly value="{{ route('polls.show', $poll) }}" class="block w-full rounded-xl border-gray-300 bg-gray-50 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900">

                            <button type="button" id="copy-poll-link-button" class="w-full inline-flex items-center justify-center rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-800 transition">
                                Copy Link
                            </button>
                        </div>

                        <p id="copy-link-feedback" class="mt-3 text-sm text-green-600 hidden">
                            Copied!
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Copy link
        const copyButton = document.getElementById('copy-poll-link-button');
        const linkInput = document.getElementById('public-poll-link');
        const feedback = document.getElementById('copy-link-feedback');

        if (copyButton && linkInput) {
            copyButton.addEventListener('click', async function () {
                try {
                    await navigator.clipboard.writeText(linkInput.value);
                } catch {
                    linkInput.select();
                    document.execCommand('copy');
                }

                feedback.classList.remove('hidden');
                setTimeout(() => feedback.classList.add('hidden'), 2000);
            });
        }

        // Live results via WebSocket
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
                    if (!rowElement) return;

                    const voteCount = rowElement.querySelector('.result-vote-count');
                    const percentage = rowElement.querySelector('.result-percentage');
                    const bar = rowElement.querySelector('.result-bar');

                    if (voteCount) voteCount.textContent = row.vote_count;
                    if (percentage) percentage.textContent = Number(row.percentage).toFixed(1);
                    if (bar) bar.style.width = `${row.percentage}%`;
                });
            });
    });
</script>
</x-app-layout>
