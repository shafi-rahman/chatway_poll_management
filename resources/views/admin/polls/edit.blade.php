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

                <form method="POST" action="{{ route('admin.polls.update', $poll) }}" class="px-6 py-6 space-y-8">
                    @csrf
                    @method('PUT')
                    <div>
                        <label for="question" class="block text-sm font-medium text-gray-700">
                            Poll Question
                        </label>
                        <input id="question" name="question" type="text" value="{{ old('question', $poll->question) }}" placeholder="e.g. Which feature should we build next in Chatway?" class="mt-2 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                        @error('question')
                            <p class="mt-2 text-sm text-red-600" data-error="question">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid gap-6 md:grid-cols-3">
                        <div>
                            <label for="is_active" class="block text-sm font-medium text-gray-700">
                                Poll Status
                            </label>
                            <select id="is_active" name="is_active" class="mt-2 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900" >
                                <option value="1" {{ old('is_active', (string) (int) $poll->is_active) == '1' ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ old('is_active', (string) (int) $poll->is_active) == '0' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>

                        <div>
                            <label for="starts_at" class="block text-sm font-medium text-gray-700">
                                Start Date & Time
                            </label>
                            <input id="starts_at" name="starts_at" type="datetime-local" value="{{ old('starts_at', optional($poll->starts_at)->format('Y-m-d\TH:i')) }}" class="mt-2 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                            @error('starts_at')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="ends_at" class="block text-sm font-medium text-gray-700">
                                End Date & Time
                            </label>
                            <input id="ends_at" name="ends_at" type="datetime-local" value="{{ old('ends_at', optional($poll->ends_at)->format('Y-m-d\TH:i')) }}" class="mt-2 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                            @error('ends_at')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">
                                    Poll Options
                                </label>
                                <p class="mt-1 text-sm text-gray-500">
                                    Keep at least two valid options.
                                </p>
                            </div>

                            <button type="button" id="add-option-button" class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                                Add Option
                            </button>
                        </div>

                        @php
                            $viewOptions = [];
                            if (old('options')) {
                                foreach (old('options') as $oldOpt) {
                                    $id = !empty($oldOpt['id']) ? (int) $oldOpt['id'] : null;
                                    $existing = $id ? $poll->options->firstWhere('id', $id) : null;
                                    $viewOptions[] = [
                                        'id'         => $id,
                                        'text'       => $oldOpt['text'] ?? '',
                                        'is_active'  => isset($oldOpt['is_active']) ? (bool) $oldOpt['is_active'] : true,
                                        'has_votes'  => $existing ? $existing->votes_count > 0 : false,
                                        'vote_count' => $existing ? $existing->vote_count : 0,
                                    ];
                                }
                            } else {
                                foreach ($poll->options as $option) {
                                    $viewOptions[] = [
                                        'id'         => $option->id,
                                        'text'       => $option->option_text,
                                        'is_active'  => $option->is_active,
                                        'has_votes'  => $option->votes_count > 0,
                                        'vote_count' => $option->vote_count,
                                    ];
                                }
                            }
                        @endphp

                        <div id="options-wrapper" class="mt-4 space-y-4">
                            @foreach ($viewOptions as $i => $opt)
                                <div class="rounded-xl border {{ $opt['has_votes'] ? 'border-amber-200 bg-amber-50/30' : 'border-gray-200' }} p-4 option-item">

                                    <input type="hidden" name="options[{{ $i }}][id]" value="{{ $opt['id'] ?? '' }}">

                                    <div class="flex items-start gap-4">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2">
                                                <label class="block text-sm font-medium text-gray-700 option-label">
                                                    Option {{ $i + 1 }}
                                                </label>

                                                <span class="text-xs font-medium text-gray-500">
                                                    {{ $opt['vote_count'] }} {{ $opt['vote_count'] === 1 ? 'vote' : 'votes' }}
                                                    @if($opt['has_votes'])
                                                        - <span class="text-amber-600">locked</span>
                                                    @endif
                                                </span>
                                            </div>
                                            <input type="text" name="options[{{ $i }}][text]" value="{{ $opt['text'] }}" placeholder="Enter option"
                                                   class="mt-2 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                                        </div>

                                        <div class="is-active-container mt-7 flex flex-col items-center gap-1 min-w-[52px]">
                                            <input type="hidden" name="options[{{ $i }}][is_active]" value="0">

                                            <input type="checkbox" id="is_active_opt_{{ $i }}" name="options[{{ $i }}][is_active]" value="1"
                                                class="h-4 w-4 rounded border-gray-300 text-gray-900 focus:ring-gray-900"
                                                {{ $opt['is_active'] ? 'checked' : '' }}>

                                            <label for="is_active_opt_{{ $i }}" class="text-xs text-gray-500 cursor-pointer select-none">
                                                Active
                                            </label>
                                        </div>

                                        <button type="button"
                                                class="remove-option-button mt-7 inline-flex items-center rounded-lg border px-3 py-2 text-sm font-medium transition
                                                       {{ $opt['has_votes'] ? 'border-gray-200 bg-gray-50 text-gray-400 cursor-not-allowed opacity-50' : 'border-red-200 bg-red-50 text-red-700 hover:bg-red-100' }}"
                                                {{ $opt['has_votes'] ? 'disabled' : '' }}
                                                title="{{ $opt['has_votes'] ? 'Cannot remove - this option has votes' : '' }}">
                                            Remove
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        @error('options')
                            <p class="mt-2 text-sm text-red-600" data-error="options">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-6">
                        <a href="{{ route('admin.polls.index') }}" class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                            Cancel
                        </a>

                        <button
                            type="submit"
                            class="inline-flex items-center rounded-lg bg-gray-900 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-gray-800 transition">
                            Update Poll
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const wrapper = document.getElementById('options-wrapper');
        const addButton = document.getElementById('add-option-button');
        const questionInput = document.getElementById('question');

        let optionIndex = wrapper.querySelectorAll('.option-item').length;

        function updateLabels() {
            const items = wrapper.querySelectorAll('.option-item');
            const totalCount = items.length;
            const lockAll = totalCount <= 2;

            items.forEach((item, index) => {
                const label = item.querySelector('.option-label');
                if (label) label.textContent = `Option ${index + 1}`;
            });

            wrapper.querySelectorAll('.remove-option-button:not([disabled])').forEach((btn) => {
                btn.disabled = lockAll;
                btn.classList.toggle('opacity-50', lockAll);
                btn.classList.toggle('cursor-not-allowed', lockAll);
            });

            wrapper.querySelectorAll('.is-active-container').forEach((container) => {
                const cb = container.querySelector('input[type="checkbox"]');
                if (lockAll) {
                    if (cb) cb.checked = true;
                    container.classList.add('opacity-50', 'pointer-events-none');
                } else {
                    container.classList.remove('opacity-50', 'pointer-events-none');
                }
            });
        }

        function createOption() {
            const div = document.createElement('div');
            div.className = 'rounded-xl border border-gray-200 p-4 option-item';

            div.innerHTML = `
                <input type="hidden" name="options[${optionIndex}][id]" value="">
                <input type="hidden" name="options[${optionIndex}][is_active]" value="1">
                <div class="flex items-start gap-4">
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 option-label">Option</label>
                        <input type="text" name="options[${optionIndex}][text]" placeholder="Enter option" class="mt-2 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                    </div>
                    <button type="button" class="remove-option-button mt-7 inline-flex items-center rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm font-medium text-red-700 hover:bg-red-100 transition">
                        Remove
                    </button>
                </div>
            `;

            optionIndex++;
            return div;
        }

        addButton.addEventListener('click', function () {
            wrapper.appendChild(createOption());
            updateLabels();
        });

        wrapper.addEventListener('click', function (e) {
            const btn = e.target.closest('.remove-option-button');
            if (!btn || btn.disabled) return;

            const items = wrapper.querySelectorAll('.option-item');
            if (items.length <= 2) return;

            btn.closest('.option-item').remove();
            updateLabels();
        });

        if (questionInput) {
            questionInput.addEventListener('input', function () {
                const error = document.querySelector('[data-error="question"]');
                if (error) error.remove();
            });
        }

        document.addEventListener('input', function (e) {
            if (e.target.name && e.target.name.includes('[text]')) {
                const optionError = document.querySelector('[data-error="options"]');
                if (optionError) optionError.remove();
            }
        });

        updateLabels();
    });
</script>
</x-app-layout>