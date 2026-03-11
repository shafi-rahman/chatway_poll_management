<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Poll;

class PollController extends Controller
{
    
    //listing the poll
    public function index(Request $request): View
    {
        $polls = Poll::query()
            ->where('user_id', $request->user()->id)
            ->withCount(['options', 'votes'])
            ->latest()
            ->paginate(10);

        return view('admin.polls.index', compact('polls'));
    }

    //creating poll
    public function create(): View
    {
        return view('admin.polls.create');
    }

    //store poll
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'question' => ['required', 'string', 'max:255'],
            'options' => [
                'required',
                'array',
                function ($attribute, $value, $fail) {
                    $cleanOptions = collect($value)
                        ->map(fn ($option) => is_string($option) ? trim($option) : '')
                        ->filter(fn ($option) => $option !== '')
                        ->unique()
                        ->values();

                    if ($cleanOptions->count() < 2) {
                        $fail('Please provide at least two valid poll options.');
                    }
                },
            ],
            'options.*' => ['nullable', 'string', 'max:255'],
        ]);

        $cleanOptions = collect($validated['options'])
            ->map(fn ($option) => is_string($option) ? trim($option) : '')
            ->filter(fn ($option) => $option !== '')
            ->unique()
            ->values();

        DB::transaction(function () use ($request, $validated, $cleanOptions): void {
            $poll = Poll::create([
                'user_id' => $request->user()->id,
                'question' => trim($validated['question']),
                'is_active' => true,
            ]);

            $poll->options()->createMany(
                $cleanOptions->map(function ($option, $index) {
                    return [
                        'option_text' => $option,
                        'sort_order' => $index + 1,
                    ];
                })->all()
            );
        });

        return redirect()
            ->route('admin.polls.index')
            ->with('success', 'Poll created successfully.');
    }
}


