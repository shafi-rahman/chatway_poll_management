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
            'is_active' => ['required', 'boolean'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after:starts_at'],
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
                'is_active' => (bool) $validated['is_active'],
                'starts_at' => $validated['starts_at'] ?? null,
                'ends_at' => $validated['ends_at'] ?? null,
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

    // edit poll
    public function edit(Request $request, Poll $poll): View|RedirectResponse
    {
        if ($poll->user_id !== $request->user()->id) {
            abort(403, 'You are not authorized to edit this poll.');
        }

        if ($poll->votes()->exists()) {
            return redirect()
                ->route('admin.polls.index')
                ->with('error', 'This poll can no longer be edited because votes have already been recorded.');
        }

        $poll->load('options');

        return view('admin.polls.edit', compact('poll'));
    }

    // update poll
    public function update(Request $request, Poll $poll): RedirectResponse
    {
        if ($poll->user_id !== $request->user()->id) {
            abort(403, 'You are not authorized to update this poll.');
        }

        if ($poll->votes()->exists()) {
            return redirect()
                ->route('admin.polls.index')
                ->with('error', 'This poll can no longer be edited because votes have already been recorded.');
        }

        $validated = $request->validate([
            'question' => ['required', 'string', 'max:255'],
            'is_active' => ['required', 'boolean'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after:starts_at'],
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

        DB::transaction(function () use ($poll, $validated, $cleanOptions): void {
            $poll->update([
                'question' => trim($validated['question']),
                'is_active' => (bool) $validated['is_active'],
                'starts_at' => $validated['starts_at'] ?? null,
                'ends_at' => $validated['ends_at'] ?? null,
            ]);

            $poll->options()->delete();

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
            ->with('success', 'Poll updated successfully.');
    }

    // show poll
    public function show(Request $request, Poll $poll): View
    {
        if ($poll->user_id !== $request->user()->id) {
            abort(403, 'You are not authorized to view this poll.');
        }

        $poll->load(['options', 'user']);
        $poll->loadCount(['votes', 'options']);

        return view('admin.polls.show', compact('poll'));
    }

}


