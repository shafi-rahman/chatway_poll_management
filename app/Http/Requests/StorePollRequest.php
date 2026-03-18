<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class StorePollRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'question'  => ['required', 'string', 'max:255'],
            'is_active' => ['required', 'boolean'],
            'starts_at' => ['nullable', 'date', 'required_if:is_active,1'],
            'ends_at'   => ['nullable', 'date', 'after:starts_at', 'required_if:is_active,1'],
            'options'   => [
                'required',
                'array',
                function ($attribute, $value, $fail) {
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
