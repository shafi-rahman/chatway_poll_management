<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Poll\PollData;
use App\Exceptions\PollDomainException;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePollRequest;
use App\Http\Requests\UpdatePollRequest;
use App\Models\Poll;
use App\Services\PollService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PollController extends Controller
{
    public function __construct(private PollService $pollService) {}

    public function index(Request $request): View
    {
        $polls = $this->pollService->getPaginatedPolls($request->user()->id);

        return view('admin.polls.index', compact('polls'));
    }

    public function create(): View
    {
        return view('admin.polls.create');
    }

    public function store(StorePollRequest $request): RedirectResponse
    {
        try {
            $data = PollData::forCreate($request->validated());
        } catch (PollDomainException $e) {
            return back()->withErrors([$e->field => $e->getMessage()])->withInput();
        }

        $this->pollService->createPoll($request->user(), $data);

        return redirect()
            ->route('admin.polls.index')
            ->with('success', 'Poll created successfully.');
    }

    public function edit(Request $request, Poll $poll): View
    {
        if ($poll->user_id !== $request->user()->id) {
            abort(403, 'You are not authorized to edit this poll.');
        }

        return view('admin.polls.edit', [
            'poll' => $this->pollService->getPollForEdit($poll),
        ]);
    }

    public function update(UpdatePollRequest $request, Poll $poll): RedirectResponse
    {
        if ($poll->user_id !== $request->user()->id) {
            abort(403, 'You are not authorized to update this poll.');
        }

        try {
            $data = PollData::forUpdate($request->validated());
        } catch (PollDomainException $e) {
            return back()->withErrors([$e->field => $e->getMessage()])->withInput();
        }

        $this->pollService->updatePoll($poll, $data);

        return redirect()
            ->route('admin.polls.index')
            ->with('success', 'Poll updated successfully.');
    }

    public function show(Request $request, Poll $poll): View
    {
        if ($poll->user_id !== $request->user()->id) {
            abort(403, 'You are not authorized to view this poll.');
        }

        return view('admin.polls.show', $this->pollService->getPollWithDetails($poll));
    }

    public function results(Request $request, Poll $poll): RedirectResponse
    {
        if ($poll->user_id !== $request->user()->id) {
            abort(403, 'You are not authorized to view these results.');
        }

        return redirect()->route('admin.polls.show', $poll);
    }
}
