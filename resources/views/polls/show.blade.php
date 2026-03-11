<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $poll->question }} | {{ config('app.name', 'Chatway Poll Management') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-gray-100 font-sans text-gray-900 antialiased">
    <div class="mx-auto flex min-h-screen max-w-4xl items-center px-4 py-10 sm:px-6 lg:px-8">
        <div class="w-full" data-poll-uuid="{{ $poll->uuid }}">
            <div class="mb-6 text-center">
                <img src="https://files-cdn.chatway.app/assets/images/logo-text.svg" alt="Chatway" class="mx-auto w-48" />
            </div>

            <div class="overflow-hidden rounded-3xl border border-gray-100 bg-white shadow-sm">
                <div class="border-b border-gray-100 px-6 py-5 sm:px-8">
                    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
                        <span id="poll-status-badge" class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium {{ $poll->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                            {{ $poll->is_active ? 'Active' : 'Inactive' }}
                        </span>

                        <div class="flex flex-col items-end gap-1 text-right">
                            @if ($poll->starts_at)
                                <span class="text-xs text-gray-900">
                                    Starts {{ $poll->starts_at->format('M d, Y h:i A') }}
                                </span>
                            @endif

                            @if ($poll->ends_at)
                                <span class="text-xs text-red-900">
                                    Ends {{ $poll->ends_at->format('M d, Y h:i A') }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <div id="poll-message-stack">
                        @if (session('success'))
                            <div class="mb-6 session-message rounded-2xl border text-center border-green-200 bg-green-50 px-4 py-4 text-sm text-green-700">
                                {{ session('success') }}
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="mb-6 session-message rounded-2xl border text-center border-red-200 bg-red-50 px-4 py-4 text-sm text-red-700">
                                {{ session('error') }}
                            </div>
                        @endif

                        @if (!$poll->is_active)
                            <div class="mb-6 rounded-2xl border text-center border-red-200 bg-red-50 px-4 py-4 text-sm text-red-700">
                                This poll is currently inactive and not accepting responses.
                            </div>
                        @elseif (!$hasStarted)
                            <div class="mb-6 rounded-2xl border text-center border-blue-200 bg-blue-50 px-4 py-4 text-sm text-blue-700">
                                This poll is scheduled and will open on {{ $poll->starts_at?->format('M d, Y h:i A') }}.
                            </div>
                        @elseif ($hasEnded)
                            <div class="mb-6 rounded-2xl border text-center border-amber-200 bg-amber-50 px-4 py-4 text-sm text-amber-700">
                                This poll has ended and is no longer accepting votes.
                            </div>
                        @elseif ($hasAlreadyVoted)
                            <div id="already-voted-message" class="mb-6 rounded-2xl border text-center border-purple-200 bg-purple-50 px-4 py-4 text-sm text-purple-700">
                                You have already voted on this poll.
                            </div>
                        @else
                            <div id="live-message" class="mb-6 rounded-2xl border text-center border-green-200 bg-green-50 px-4 py-4 text-sm text-green-700">
                                This poll is live and ready for voting.
                            </div>
                        @endif
                    </div>

                    <p class="mt-3 text-right text-sm text-gray-500">
                        Created by {{ $poll->user->name }}
                    </p>
                </div>

                <div class="px-6 py-6 sm:px-8">
                    <h3 class="pb-3 text-xl font-bold tracking-tight text-gray-700 sm:text-2xl">
                        {{ $poll->question }}
                    </h3>

                    <form id="poll-vote-form" method="POST" action="{{ route('polls.vote', $poll) }}" class="space-y-4">
                        @csrf

                        @foreach ($poll->options as $option)
                            <label class="flex cursor-pointer items-start gap-4 rounded-2xl border border-gray-200 px-4 py-4 transition hover:border-gray-300 hover:bg-gray-50">
                                <input type="radio" name="poll_option_id" value="{{ $option->id }}" class="poll-option-input mt-1 h-4 w-4 border-gray-300 text-gray-900 focus:ring-gray-900"
                                    {{ (!$isAvailableForVoting || $hasAlreadyVoted) ? 'disabled' : '' }}
                                    {{ old('poll_option_id') == $option->id ? 'checked' : '' }} >

                                <div class="min-w-0 flex-1">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $option->option_text }}
                                    </div>
                                </div>
                            </label>
                        @endforeach

                        <p id="poll-option-error" class="hidden text-center text-sm text-red-600"></p>

                        <div class="flex justify-center pt-2">
                            <button id="submit-vote-button" type="submit" class="inline-flex items-center rounded-xl bg-gray-900 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-gray-800 disabled:cursor-not-allowed disabled:opacity-50"
                                {{ (!$isAvailableForVoting || $hasAlreadyVoted) ? 'disabled' : '' }} >
                                Submit Vote
                            </button>
                        </div>
                    </form>
                </div>

                <div class="mt-8 rounded-2xl border border-gray-100 bg-gray-50 p-5">
                    <div class="mb-4 flex items-center justify-between gap-4">
                        <h4 class="text-base font-semibold text-gray-900">
                            Current Results
                        </h4>
                        <span class="text-sm text-gray-500">
                            Total Votes: <span id="public-results-total-votes">{{ $totalVotes }}</span>
                        </span>
                    </div>

                    <div id="public-results-breakdown" class="space-y-4">
                        @foreach ($resultRows as $row)
                            <div class="public-result-row" data-option-id="{{ $row['id'] }}">
                                <div class="mb-2 flex items-center justify-between gap-4">
                                    <div class="text-sm font-medium text-gray-800">
                                        {{ $row['option_text'] }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        <span class="public-result-vote-count">{{ $row['vote_count'] }}</span> votes -
                                        <span class="public-result-percentage">{{ number_format($row['percentage'], 1) }}</span>%
                                    </div>
                                </div>

                                <div class="h-2.5 overflow-hidden rounded-full bg-gray-200">
                                    <div class="public-result-bar h-full rounded-full bg-gray-900 transition-all duration-300" style="width: {{ $row['percentage'] }}%;"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>
        </div>
    </div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.session-message').forEach(function (element) {
            setTimeout(function () {
                element.classList.add('opacity-0');
                setTimeout(function () {
                    element.remove();
                }, 500);
            }, 3000);
        });

        const container = document.querySelector('[data-poll-uuid]');
        const form = document.getElementById('poll-vote-form');
        const submitButton = document.getElementById('submit-vote-button');
        const optionInputs = document.querySelectorAll('.poll-option-input');
        const optionError = document.getElementById('poll-option-error');
        const messageStack = document.getElementById('poll-message-stack');
        const totalVotesElement = document.getElementById('public-results-total-votes');

        function clearVoteError() {
            if (optionError) {
                optionError.textContent = '';
                optionError.classList.add('hidden');
            }
        }

        function showVoteError(message) {
            if (optionError) {
                optionError.textContent = message;
                optionError.classList.remove('hidden');
            }
        }

        function prependMessage(message, type = 'success') {
            if (!messageStack) return;

            const colorMap = {
                success: 'border-green-200 bg-green-50 text-green-700',
                error: 'border-red-200 bg-red-50 text-red-700',
                voted: 'border-purple-200 bg-purple-50 text-purple-700',
            };

            const div = document.createElement('div');
            div.className = `mb-6 rounded-2xl border text-center px-4 py-4 text-sm ${colorMap[type] ?? colorMap.success}`;
            div.textContent = message;

            messageStack.prepend(div);

            setTimeout(() => {
                div.classList.add('opacity-0');
                setTimeout(() => div.remove(), 500);
            }, 3000);
        }

        function disableVotingUI() {
            optionInputs.forEach((input) => {
                input.disabled = true;
            });

            if (submitButton) {
                submitButton.disabled = true;
            }
        }

        function updateResultsUI(resultRows, totalVotes) {
            if (totalVotesElement) {
                totalVotesElement.textContent = totalVotes;
            }

            (resultRows || []).forEach((row) => {
                const rowElement = document.querySelector(`.public-result-row[data-option-id="${row.id}"]`);
                if (!rowElement) return;

                const voteCount = rowElement.querySelector('.public-result-vote-count');
                const percentage = rowElement.querySelector('.public-result-percentage');
                const bar = rowElement.querySelector('.public-result-bar');

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
        }

        optionInputs.forEach((input) => {
            input.addEventListener('change', clearVoteError);
        });

        if (form) {
            form.addEventListener('submit', async function (event) {
                event.preventDefault();
                clearVoteError();

                const selectedOption = form.querySelector('input[name="poll_option_id"]:checked');

                if (!selectedOption) {
                    showVoteError('Please select a valid poll option.');
                    return;
                }

                if (submitButton) {
                    submitButton.disabled = true;
                    submitButton.textContent = 'Submitting...';
                }

                try {
                    const formData = new FormData(form);

                    const response = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: formData,
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        const message = data.message || data.errors?.poll_option_id?.[0] || 'Unable to submit your vote.';
                        showVoteError(message);

                        if (submitButton) {
                            submitButton.disabled = false;
                            submitButton.textContent = 'Submit Vote';
                        }

                        return;
                    }

                    updateResultsUI(data.result_rows, data.total_votes);
                    disableVotingUI();
                    prependMessage(data.message || 'Your vote has been submitted successfully.', 'success');

                    const liveMessage = document.getElementById('live-message');
                    if (liveMessage) {
                        liveMessage.remove();
                    }

                    if (!document.getElementById('already-voted-message') && messageStack) {
                        const votedMessage = document.createElement('div');
                        votedMessage.id = 'already-voted-message';
                        votedMessage.className = 'mb-6 rounded-2xl border text-center border-purple-200 bg-purple-50 px-4 py-4 text-sm text-purple-700';
                        votedMessage.textContent = 'You have already voted on this poll.';
                        messageStack.appendChild(votedMessage);
                    }

                    if (submitButton) {
                        submitButton.textContent = 'Vote Submitted';
                    }
                } catch (error) {
                    showVoteError('Something went wrong while submitting your vote.');

                    if (submitButton) {
                        submitButton.disabled = false;
                        submitButton.textContent = 'Submit Vote';
                    }
                }
            });
        }

        if (!container || typeof window.Echo === 'undefined') {
            return;
        }

        const pollUuid = container.dataset.pollUuid;

        window.Echo.channel(`poll.${pollUuid}`)
            .listen('.poll.vote.updated', (event) => {
                updateResultsUI(event.result_rows || [], event.total_votes || 0);
            });
    });
</script>
</body>

</html>