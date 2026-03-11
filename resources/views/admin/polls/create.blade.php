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

            <a
                href="{{ route('admin.polls.index') }}"
                class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition"
            >
                Back to Polls
            </a>
        </div>
    </x-slot>

    <div class="py-10">
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

                <form method="POST" action="{{ route('admin.polls.store') }}" class="px-6 py-6 space-y-8">
                    @csrf

                    <div>
                        <label for="question" class="block text-sm font-medium text-gray-700">
                            Poll Question
                        </label>
                        <input
                            id="question"
                            name="question"
                            type="text"
                            value="{{ old('question') }}"
                            placeholder="e.g. Which feature should we build next in Chatway?"
                            class="mt-2 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900"
                        >
                        @error('question')
                            <p class="mt-2 text-sm text-red-600" data-error="question">
                                {{ $message }}
                            </p>
                        @enderror
                        <p class="mt-2 text-sm text-gray-500">
                            Keep it short and clear so voters can respond quickly.
                        </p>
                    </div>

                    <div>
                        <div class="flex items-center justify-between">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">
                                    Poll Options
                                </label>
                                <p class="mt-1 text-sm text-gray-500">
                                    Add at least two answer choices.
                                </p>
                            </div>

                            <button
                                type="button"
                                id="add-option-button"
                                class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition"
                            >
                                Add Option
                            </button>
                        </div>

                        @php
                            $oldOptions = old('options', ['', '']);
                        @endphp

                        <div id="options-wrapper" class="mt-4 space-y-4">
                            @foreach ($oldOptions as $index => $option)
                                <div class="rounded-xl border border-gray-200 p-4 option-item">
                                    <div class="flex items-start justify-between gap-4">
                                        <div class="flex-1">
                                            <label class="block text-sm font-medium text-gray-700 option-label">
                                                Option {{ $index + 1 }}
                                            </label>
                                            <input
                                                type="text"
                                                name="options[]"
                                                value="{{ $option }}"
                                                placeholder="Enter option text"
                                                class="mt-2 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900"
                                            >
                                        </div>

                                        <button
                                            type="button"
                                            class="remove-option-button mt-7 inline-flex items-center rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm font-medium text-red-700 hover:bg-red-100 transition"
                                        >
                                            Remove
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        @error('options')
                            <p class="mt-2 text-sm text-red-600" data-error="options">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-6">
                        <a
                            href="{{ route('admin.polls.index') }}"
                            class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition"
                        >
                            Cancel
                        </a>

                        <button
                            type="submit"
                            class="inline-flex items-center rounded-lg bg-gray-900 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-gray-800 transition"
                        >
                            Save Poll
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {

            const optionsWrapper = document.getElementById('options-wrapper');
            const addOptionButton = document.getElementById('add-option-button');
            const questionInput = document.getElementById('question');

            if (questionInput) {
                questionInput.addEventListener('input', function () {
                    const error = document.querySelector('[data-error="question"]');
                    if (error) error.remove();
                });
            }
            document.addEventListener('input', function (e) {
                if (e.target.name === 'options[]') {
                    const optionError = document.querySelector('[data-error="options"]');
                    if (optionError) optionError.remove();
                }
            });

            function updateOptionLabels() {
                const optionItems = optionsWrapper.querySelectorAll('.option-item');

                optionItems.forEach((item, index) => {
                    const label = item.querySelector('.option-label');
                    if (label) {
                        label.textContent = `Option ${index + 1}`;
                    }
                });

                const removeButtons = optionsWrapper.querySelectorAll('.remove-option-button');
                removeButtons.forEach((button) => {
                    button.disabled = optionItems.length <= 2;
                    button.classList.toggle('opacity-50', optionItems.length <= 2);
                    button.classList.toggle('cursor-not-allowed', optionItems.length <= 2);
                });
            }

            function createOptionItem(value = '') {
                const wrapper = document.createElement('div');
                wrapper.className = 'rounded-xl border border-gray-200 p-4 option-item';

                wrapper.innerHTML = `
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-700 option-label">
                                Option
                            </label>
                            <input
                                type="text"
                                name="options[]"
                                value="${value}"
                                placeholder="Enter option text"
                                class="mt-2 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900"
                            >
                        </div>

                        <button
                            type="button"
                            class="remove-option-button mt-7 inline-flex items-center rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm font-medium text-red-700 hover:bg-red-100 transition"
                        >
                            Remove
                        </button>
                    </div>
                `;

                return wrapper;
            }

            addOptionButton.addEventListener('click', function () {
                optionsWrapper.appendChild(createOptionItem());
                updateOptionLabels();
            });

            optionsWrapper.addEventListener('click', function (event) {
                const removeButton = event.target.closest('.remove-option-button');

                if (!removeButton) {
                    return;
                }

                const optionItems = optionsWrapper.querySelectorAll('.option-item');

                if (optionItems.length <= 2) {
                    return;
                }

                removeButton.closest('.option-item')?.remove();
                updateOptionLabels();
            });

            updateOptionLabels();
        });
    </script>
</x-app-layout>