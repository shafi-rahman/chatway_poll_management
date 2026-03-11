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

            <a href="{{ route('admin.polls.index') }}"
                class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                Back to Polls
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="rounded-2xl border border-gray-100 bg-white shadow-sm">

                <div class="border-b border-gray-100 px-6 py-6">
                    <h3 class="text-lg font-semibold text-gray-900">
                        Poll Details
                    </h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Start by writing the question and adding at least two options.
                    </p>
                </div>

                <form method="POST" action="" class="px-6 py-6 space-y-8">
                    @csrf

                    <div class="mb-6">
                        <label for="question" class="block text-sm font-medium text-gray-700">
                            Poll Question
                        </label>
                        <input id="question" name="question" type="text" placeholder="e.g. Which feature should we build next in Chatway?" class="mt-2 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900" >
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

                            <button type="button" id="add-option-button" class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition" >
                                Add Option
                            </button>
                        </div>

                        <div id="options-wrapper" class="mt-4 space-y-4">

                            <div class="rounded-xl border border-gray-200 p-4 option-item">
                                <div class="flex items-start gap-4">

                                    <div class="flex-1">
                                        <label class="block text-sm font-medium text-gray-700 option-label">
                                            Option 1
                                        </label>
                                        <input type="text" name="options[]" placeholder="Enter first option" class="mt-2 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900" >
                                    </div>

                                </div>
                            </div>

                            <div class="rounded-xl border border-gray-200 p-4 option-item">
                                <div class="flex items-start gap-4">

                                    <div class="flex-1">
                                        <label class="block text-sm font-medium text-gray-700 option-label">
                                            Option 2
                                        </label>
                                        <input type="text" name="options[]" placeholder="Enter second option" class="mt-2 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900" >
                                    </div>

                                </div>
                            </div>

                        </div>

                    </div>

                    <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-6">

                        <a href="{{ route('admin.polls.index') }}"
                           class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                            Cancel
                        </a>

                        <button type="submit"
                            class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                            Save Poll
                        </button>

                    </div>

                </form>

            </div>
        </div>
    </div>

<script>

document.addEventListener('DOMContentLoaded', () => {

    const optionsWrapper = document.getElementById('options-wrapper');
    const addOptionBtn = document.getElementById('add-option-button');

    // Update option labels and control remove button state
    const refreshOptions = () => {

        const optionItems = optionsWrapper.querySelectorAll('.option-item');

        optionItems.forEach((item, index) => {
            const label = item.querySelector('.option-label');
            label.textContent = `Option ${index + 1}`;
        });

        const removeButtons = optionsWrapper.querySelectorAll('.remove-option-button');

        removeButtons.forEach(button => {

            const disable = optionItems.length <= 2;

            button.disabled = disable;
            button.classList.toggle('opacity-50', disable);
            button.classList.toggle('cursor-not-allowed', disable);

        });

    };

    // Create a new option element
    const createOptionItem = () => {

        const optionDiv = document.createElement('div');
        optionDiv.className = "rounded-xl border border-gray-200 p-4 option-item";

        optionDiv.innerHTML = `
            <div class="flex items-start gap-4" style="align-items: flex-end;">

                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 option-label">
                        Option
                    </label>

                    <input type="text" name="options[]" placeholder="Enter option" class="mt-2 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
                </div>

                <button type="button" class="remove-option-button mt-7 inline-flex items-center rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm font-medium text-red-700 hover:bg-red-100 transition">
                    Remove
                </button>

            </div>
        `;

        return optionDiv;

    };

    // Add new option
    addOptionBtn.addEventListener('click', () => {

        optionsWrapper.appendChild(createOptionItem());
        refreshOptions();

    });

    // Handle option removal using event delegation
    optionsWrapper.addEventListener('click', (event) => {

        if (!event.target.classList.contains('remove-option-button')) return;

        const optionItems = optionsWrapper.querySelectorAll('.option-item');

        if (optionItems.length <= 2) return;

        event.target.closest('.option-item').remove();
        refreshOptions();

    });

    // Initial setup
    refreshOptions();

});

</script>

</x-app-layout>