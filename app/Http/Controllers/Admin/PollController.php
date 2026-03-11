<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Poll;

class PollController extends Controller
{
    
    //listing the poll
    public function index(): View
    {
        return view('admin.polls.index');
    }

    //creating poll
    public function create(): View
    {
        return view('admin.polls.create');
    }

    //store poll
    public function store(Request $request)
    {
        $validated = $request->validate([
            'question' => ['required', 'string', 'max:255'],
            'options' => ['required', 'array', 'min:2'],
            'options.*' => ['required', 'string', 'max:255'],
        ]);

        $cleanOptions = collect($validated['options'])
            ->map(fn ($option) => trim($option))
            ->filter(fn ($option) => $option !== '')
            ->values();

        if ($cleanOptions->count() < 2) {
            return back()
                ->withErrors([
                    'options' => 'Please provide at least two valid poll options.',
                ])
                ->withInput();
        }

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


