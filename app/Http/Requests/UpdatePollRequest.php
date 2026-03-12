<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Collection;

class UpdatePollRequest extends FormRequest
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
            'starts_at' => ['nullable', 'date'],
            'ends_at'   => ['nullable', 'date', 'after:starts_at'],
            'options'   => [
                'required',
                'array',
                function ($attribute, $value, $fail) {
                    if ($this->cleanOptions()->count() < 2) {
                        $fail('Please provide at least two valid poll options.');
                    }
                },
            ],
            'options.*' => ['nullable', 'string', 'max:255'],
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
