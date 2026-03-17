<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
            'options' => ['required', 'array',
                function ($attribute, $value, $fail) {

                    $options = collect($value);

                    $valid = $options->filter(fn ($o) => filled($o['text'] ?? null))->count();
                    if ($valid < 2) {
                        $fail('Please provide at least two valid poll options.');
                    }

                    $active = $options->filter( fn ($o) => filled($o['text'] ?? null) && (($o['is_active'] ?? 0) == 1) )->count();
                    if ($active < 2) {
                        $fail('A poll must have at least two active options.');
                    }
                },
            ],
            'options.*.id'        => ['nullable', 'integer'],
            'options.*.text'      => ['nullable', 'string', 'max:255'],
            'options.*.is_active' => ['nullable', 'boolean'],
        ];
    }
}