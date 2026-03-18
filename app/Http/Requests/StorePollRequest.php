<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

class StorePollRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'question'  => [
                'required', 'string', 'max:255',
                Rule::unique('polls', 'question')->where('user_id', $this->user()->id),
            ],
            'is_active' => ['required', 'boolean'],
            'starts_at' => ['nullable', 'date', 'required_if:is_active,1'],
            'ends_at'   => ['nullable', 'date', 'after:starts_at', 'required_if:is_active,1'],
            'options'   => [
                'required',
                'array',
                function ($attribute, $value, $fail) {
                    $texts = collect($value)
                        ->map(fn ($t) => strtolower(trim(is_string($t) ? $t : '')))
                        ->filter(fn ($t) => $t !== '');

                    if ($texts->count() > $texts->unique()->count()) {
                        $fail('Poll options must be unique (case-insensitive).');
                        return;
                    }

                    if ($this->boolean('is_active') && $this->cleanOptions()->count() < 2) {
                        $fail('Please provide at least two valid poll options.');
                    }
                },
            ],
            'options.*' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function withValidator($validator): void
    {
        if (! $this->boolean('is_active')) {
            return;
        }

        $validator->after(function ($validator) {
            if ($this->filled('ends_at') && Carbon::parse($this->input('ends_at'))->isPast()) {
                $validator->errors()->add('ends_at', 'The end date must be in the future for an active poll.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'question.unique'       => 'You already have a poll with this question.',
            'starts_at.required_if' => 'An active poll must have a start date.',
            'ends_at.required_if'   => 'An active poll must have an end date.',
        ];
    }

    public function cleanOptions(): Collection
    {
        return collect($this->input('options', []))
            ->map(fn ($option) => is_string($option) ? trim($option) : '')
            ->filter(fn ($option) => $option !== '')
            ->unique()
            ->values();
    }
}
