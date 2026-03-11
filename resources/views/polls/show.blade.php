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
        <div class="w-full">
            <div class="mb-6 text-center">
                <img src="https://files-cdn.chatway.app/assets/images/logo-text.svg" alt="Chatway" class="mx-auto w-48" />
            </div>

            <div class="overflow-hidden rounded-3xl border border-gray-100 bg-white shadow-sm">
                <div class="border-b border-gray-100 px-6 py-5 sm:px-8">
                    <div class="mb-6 flex flex-wrap items-center justify-between">
                        <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium {{ $poll->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                            {{ $poll->is_active ? 'Active' : 'Inactive' }}
                        </span>

                        <div>
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
                    @else
                        <div class="mb-6 rounded-2xl border text-center border-green-200 bg-green-50 px-4 py-4 text-sm text-green-700">
                            This poll is live and ready for voting.
                        </div>
                    @endif

                    <p class="mt-3 text-sm text-gray-500 text-right">
                        Created by {{ $poll->user->name }}
                    </p>

                </div>

                <div class="px-6 py-6 sm:px-8">
                    
                    <h3 class="pb-3 text-l font-bold tracking-tight text-gray-700 sm:text-2xl">
                        {{ $poll->question }}
                    </h3>

                    <form class="space-y-4">
                        @foreach ($poll->options as $option)
                            <label class="flex cursor-pointer items-start gap-4 rounded-2xl border border-gray-200 px-4 py-4 transition hover:border-gray-300 hover:bg-gray-50">
                                <input type="radio" name="poll_option_id" value="{{ $option->id }}" class="mt-1 h-4 w-4 border-gray-300 text-gray-900 focus:ring-gray-900" {{ !$isAvailableForVoting ? 'disabled' : '' }} >

                                <div class="min-w-0 flex-1">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $option->option_text }}
                                    </div>
                                </div>
                            </label>
                        @endforeach

                        <div class="pt-2 flex justify-center">
                            <button type="submit" class="inline-flex items-center rounded-xl bg-gray-900 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-gray-800 disabled:cursor-not-allowed disabled:opacity-50" {{ !$isAvailableForVoting ? 'disabled' : '' }} >
                                Submit Vote
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</body>
</html>