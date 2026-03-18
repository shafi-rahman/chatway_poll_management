@php
    $poll        = $poll ?? null;
    $isEdit      = $poll !== null;
    $action      = $isEdit ? route('admin.polls.update', $poll) : route('admin.polls.store');
    $submitLabel = $isEdit ? 'Update Poll' : 'Save Poll';
    $isActiveVal = old('is_active', $isEdit ? (string)(int)$poll->is_active : '1');

    if ($isEdit) {
        $viewOptions = [];
        if (old('options')) {
            foreach (old('options') as $oldOpt) {
                $id       = !empty($oldOpt['id']) ? (int) $oldOpt['id'] : null;
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
    } else {
        $oldOptions = old('options', ['', '']);
    }
@endphp

<form id="poll-form"
      data-mode="{{ $isEdit ? 'edit' : 'create' }}"
      method="POST"
      action="{{ $action }}"
      class="px-6 py-6 space-y-8">

    @csrf
    @if($isEdit) @method('PUT') @endif

    <div>
        <label for="question" class="block text-sm font-medium text-gray-700">
            Poll Question
        </label>
        <input id="question" name="question" type="text" value="{{ old('question', $isEdit ? $poll->question : '') }}" 
                placeholder="e.g. Which feature should we build next in Chatway?"
                class="mt-2 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
        @error('question')
            <p class="mt-2 text-sm text-red-600" data-error="question">{{ $message }}</p>
        @enderror
    </div>

    <div class="grid gap-6 md:grid-cols-3">
        <div>
            <label for="is_active" class="block text-sm font-medium text-gray-700">
                Poll Status
            </label>
            <select id="is_active" name="is_active" class="mt-2 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                <option value="1" {{ $isActiveVal == '1' ? 'selected' : '' }}>Active</option>
                <option value="0" {{ $isActiveVal == '0' ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>

        <div>
            <label for="starts_at" class="block text-sm font-medium text-gray-700">
                Start Date & Time
            </label>
            <input id="starts_at" name="starts_at" type="datetime-local" value="{{ old('starts_at', $isEdit ? optional($poll->starts_at)->format('Y-m-d\TH:i') : '') }}" class="mt-2 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
            @error('starts_at')
                <p class="mt-2 text-sm text-red-600" data-error="starts_at">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="ends_at" class="block text-sm font-medium text-gray-700">
                End Date & Time
            </label>
            <input id="ends_at" name="ends_at" type="datetime-local" value="{{ old('ends_at', $isEdit ? optional($poll->ends_at)->format('Y-m-d\TH:i') : '') }}" class="mt-2 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
            @error('ends_at')
                <p class="mt-2 text-sm text-red-600" data-error="ends_at">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div>
        <div class="flex items-center justify-between">
            <div>
                <label class="block text-sm font-medium text-gray-700">Poll Options</label>
                <p class="mt-1 text-sm text-gray-500">
                    @if($isEdit)
                        Options with votes cannot be deleted - you can edit their text or mark them inactive.
                    @else
                        Add at least two answer choices.
                    @endif
                </p>
            </div>

            <button type="button" id="add-option-button"
                    class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                Add Option
            </button>
        </div>

        <div id="options-wrapper" class="mt-4 space-y-4">
            @if($isEdit)
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
                                <input type="text" name="options[{{ $i }}][text]" value="{{ $opt['text'] }}" placeholder="Enter option" class="mt-2 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                            </div>

                            <div class="is-active-container mt-7 flex flex-col items-center gap-1 min-w-[52px]">
                                <input type="hidden" name="options[{{ $i }}][is_active]" value="0">
                                <input type="checkbox" id="is_active_opt_{{ $i }}" name="options[{{ $i }}][is_active]" value="1" class="h-4 w-4 rounded border-gray-300 text-gray-900 focus:ring-gray-900" {{ $opt['is_active'] ? 'checked' : '' }}>
                                <label for="is_active_opt_{{ $i }}" class="text-xs text-gray-500 cursor-pointer select-none">
                                    Active
                                </label>
                            </div>

                            <button type="button"
                                    class="remove-option-button mt-7 inline-flex items-center rounded-lg border px-3 py-2 text-sm font-medium transition
                                    {{ $opt['has_votes'] ? 'border-gray-200 bg-gray-50 text-gray-400 cursor-not-allowed opacity-50' : 'border-red-200 bg-red-50 text-red-700 hover:bg-red-100' }}"
                                    {{ $opt['has_votes'] ? 'disabled' : '' }} title="{{ $opt['has_votes'] ? 'Cannot remove - this option has votes' : '' }}">
                                Remove
                            </button>
                        </div>
                    </div>
                @endforeach
            @else
                @foreach ($oldOptions as $index => $option)
                    <div class="rounded-xl border border-gray-200 p-4 option-item">
                        <div class="flex items-start gap-4">
                            <div class="flex-1">
                                <label class="block text-sm font-medium text-gray-700 option-label">
                                    Option {{ $index + 1 }}
                                </label>
                                <input type="text" name="options[]" value="{{ $option }}" placeholder="Enter option" class="mt-2 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                            </div>

                            <button type="button" class="remove-option-button mt-7 inline-flex items-center rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm font-medium text-red-700 hover:bg-red-100 transition">
                                Remove
                            </button>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>

        @error('options')
            <p class="mt-2 text-sm text-red-600" data-error="options">{{ $message }}</p>
        @enderror
    </div>

    <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-6">
        <a href="{{ route('admin.polls.index') }}" class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
            Cancel
        </a>

        <button type="submit" class="inline-flex items-center rounded-lg bg-gray-900 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-gray-800 transition">
            {{ $submitLabel }}
        </button>
    </div>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form       = document.getElementById('poll-form');
        const wrapper    = document.getElementById('options-wrapper');
        const addButton  = document.getElementById('add-option-button');
        const isActiveEl = document.getElementById('is_active');
        const startsAtEl = document.getElementById('starts_at');
        const endsAtEl   = document.getElementById('ends_at');
        const questionEl = document.getElementById('question');
        const isEdit     = form.dataset.mode === 'edit';

        let optionIndex = wrapper.querySelectorAll('.option-item').length;

        function showError(fieldId, message) {
            clearError(fieldId);
            const p = document.createElement('p');
            p.className = 'mt-2 text-sm text-red-600';
            p.dataset.error = fieldId;
            p.textContent = message;

            if (fieldId === 'options') {
                wrapper.parentNode.appendChild(p);
            } else {
                const input = document.getElementById(fieldId);
                if (input) input.parentNode.appendChild(p);
            }
        }

        function clearError(fieldId) {
            const el = document.querySelector('[data-error="' + fieldId + '"]');
            if (el) el.remove();
        }

        function updateLabels() {
            const items   = wrapper.querySelectorAll('.option-item');
            const lockAll = items.length <= 2;

            items.forEach(function (item, index) {
                const label = item.querySelector('.option-label');
                if (label) label.textContent = 'Option ' + (index + 1);
            });

            wrapper.querySelectorAll('.remove-option-button:not([disabled])').forEach(function (btn) {
                btn.disabled = lockAll;
                btn.classList.toggle('opacity-50', lockAll);
                btn.classList.toggle('cursor-not-allowed', lockAll);
            });

            if (isEdit) {
                wrapper.querySelectorAll('.is-active-container').forEach(function (container) {
                    const cb = container.querySelector('input[type="checkbox"]');
                    if (lockAll) {
                        if (cb) cb.checked = true;
                        container.classList.add('opacity-50', 'pointer-events-none');
                    } else {
                        container.classList.remove('opacity-50', 'pointer-events-none');
                    }
                });
            }
        }

        function createOption() {
            const div = document.createElement('div');
            div.className = 'rounded-xl border border-gray-200 p-4 option-item';

            if (isEdit) {
                div.innerHTML =
                    '<input type="hidden" name="options[' + optionIndex + '][id]" value="">' +
                    '<input type="hidden" name="options[' + optionIndex + '][is_active]" value="1">' +
                    '<div class="flex items-start gap-4">' +
                        '<div class="flex-1">' +
                            '<label class="block text-sm font-medium text-gray-700 option-label">Option</label>' +
                            '<input type="text" name="options[' + optionIndex + '][text]" placeholder="Enter option"' +
                            ' class="mt-2 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">' +
                        '</div>' +
                        '<button type="button" class="remove-option-button mt-7 inline-flex items-center rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm font-medium text-red-700 hover:bg-red-100 transition">Remove</button>' +
                    '</div>';
                optionIndex++;
            } else {
                div.innerHTML =
                    '<div class="flex items-start gap-4">' +
                        '<div class="flex-1">' +
                            '<label class="block text-sm font-medium text-gray-700 option-label">Option</label>' +
                            '<input type="text" name="options[]" placeholder="Enter option"' +
                            ' class="mt-2 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">' +
                        '</div>' +
                        '<button type="button" class="remove-option-button mt-7 inline-flex items-center rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm font-medium text-red-700 hover:bg-red-100 transition">Remove</button>' +
                    '</div>';
            }

            return div;
        }

        addButton.addEventListener('click', function () {
            wrapper.appendChild(createOption());
            updateLabels();
            clearError('options');
        });

        wrapper.addEventListener('click', function (e) {
            const btn = e.target.closest('.remove-option-button');
            if (!btn || btn.disabled) return;
            if (wrapper.querySelectorAll('.option-item').length <= 2) return;
            btn.closest('.option-item').remove();
            updateLabels();
        });

        if (questionEl) questionEl.addEventListener('input', function () { clearError('question'); });
        if (startsAtEl) startsAtEl.addEventListener('change', function () { clearError('starts_at'); });
        if (endsAtEl)   endsAtEl.addEventListener('change',   function () { clearError('ends_at'); });
        if (isActiveEl) isActiveEl.addEventListener('change', function () {
            clearError('starts_at');
            clearError('ends_at');
            clearError('options');
        });

        wrapper.addEventListener('input', function (e) {
            var name = e.target.name || '';
            if (name === 'options[]' || name.indexOf('[text]') !== -1) clearError('options');
        });

        if (isEdit) {
            wrapper.addEventListener('change', function (e) {
                if (e.target.type === 'checkbox') clearError('options');
            });
        }

        form.addEventListener('submit', function (e) {
            try {
                clearError('question');
                clearError('starts_at');
                clearError('ends_at');
                clearError('options');

                var valid = true;

                if (!questionEl.value.trim()) {
                    showError('question', 'The question field is required.');
                    valid = false;
                }

                if (isActiveEl.value !== '1') {
                    if (!valid) e.preventDefault();
                    return;
                }

                var startsVal = startsAtEl.value;
                var endsVal   = endsAtEl.value;

                if (!startsVal) {
                    showError('starts_at', 'An active poll must have a start date.');
                    valid = false;
                }

                if (!endsVal) {
                    showError('ends_at', 'An active poll must have an end date.');
                    valid = false;
                } else if (startsVal && endsVal <= startsVal) {
                    showError('ends_at', 'The end date must be after the start date.');
                    valid = false;
                } else if (new Date(endsVal).getTime() <= Date.now()) {
                    showError('ends_at', 'The end date must be in the future for an active poll.');
                    valid = false;
                }

                var selector    = isEdit ? 'input[name$="[text]"]' : 'input[name="options[]"]';
                var filledCount = Array.from(wrapper.querySelectorAll(selector)).filter(function (i) { return i.value.trim() !== ''; }).length;

                var optTexts = Array.from(wrapper.querySelectorAll(selector))
                    .map(function (i) { return i.value.trim().toLowerCase(); })
                    .filter(function (t) { return t !== ''; });
                var uniqueOptCount = optTexts.filter(function (t, i) { return optTexts.indexOf(t) === i; }).length;

                if (uniqueOptCount < optTexts.length) {
                    showError('options', 'Poll options must be unique (case-insensitive).');
                    valid = false;
                } else if (filledCount < 2) {
                    showError('options', 'Please provide at least two valid poll options.');
                    valid = false;
                } else {
                    var activeCount = 0;
                    wrapper.querySelectorAll('.option-item').forEach(function (item) {
                        var cb = item.querySelector('input[type="checkbox"][name$="[is_active]"]');
                        if (cb) {
                            if (cb.checked) activeCount++;
                        } else {
                            activeCount++;
                        }
                    });
                    if (activeCount < 2) {
                        showError('options', 'A poll must have at least two active options.');
                        valid = false;
                    }
                }

                if (!valid) e.preventDefault();

            } catch (err) {
                e.preventDefault();
                console.error('Poll form validation error:', err);
            }
        });

        updateLabels();
    });
</script>
