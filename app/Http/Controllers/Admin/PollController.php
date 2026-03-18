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
        $this->authorize('create', Poll::class);

        return view('admin.polls.create');
    }

    public function store(StorePollRequest $request): RedirectResponse
    {
        $this->authorize('create', Poll::class);

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

    public function edit(Poll $poll): View
    {
        $this->authorize('update', $poll);

        return view('admin.polls.edit', [
            'poll' => $this->pollService->getPollForEdit($poll),
        ]);
    }

    public function update(UpdatePollRequest $request, Poll $poll): RedirectResponse
    {
        $this->authorize('update', $poll);

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

    public function show(Poll $poll): View
    {
        $this->authorize('view', $poll);

        return view('admin.polls.show', $this->pollService->getPollWithDetails($poll));
    }

    public function results(Poll $poll): RedirectResponse
    {
        $this->authorize('view', $poll);

        return redirect()->route('admin.polls.show', $poll);
    }
}
