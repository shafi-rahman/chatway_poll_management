<?php

namespace App\Http\Controllers;

use App\Http\Requests\VoteRequest;
use App\Models\Poll;
use App\Services\VoteService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PublicPollController extends Controller
{
    public function __construct(private VoteService $voteService) {}

    public function show(Request $request, Poll $poll): View
    {
        return view('polls.show', $this->voteService->getPollData($poll, $request));
    }

    public function vote(VoteRequest $request, Poll $poll): RedirectResponse|JsonResponse
    {
        [$hasStarted, $hasEnded, $isAvailableForVoting] = $this->voteService->getPollAvailability($poll);

        if (!$poll->is_active) {
            return $this->voteErrorResponse($request, $poll, 'This poll is currently inactive and not accepting votes.');
        }

        if (!$hasStarted) {
            return $this->voteErrorResponse($request, $poll, 'This poll is not open yet.');
        }

        if ($hasEnded) {
            return $this->voteErrorResponse($request, $poll, 'This poll has ended and is no longer accepting votes.');
        }

        if (!$isAvailableForVoting) {
            return $this->voteErrorResponse($request, $poll, 'This poll is not currently available for voting.');
        }

        $sessionToken = $this->voteService->ensureSessionToken($request);
        $ipAddress    = $request->ip();

        if ($this->voteService->hasAlreadyVoted($poll, $ipAddress, $sessionToken)) {
            return $this->voteErrorResponse($request, $poll, 'You have already voted on this poll.');
        }

        try {
            $result = $this->voteService->submitVote($poll, $request->integer('poll_option_id'), $ipAddress, $sessionToken);
        } catch (QueryException) {
            return $this->voteErrorResponse($request, $poll, 'You have already voted on this poll.');
        }

        if ($result === null) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Please select a valid poll option.',
                    'errors'  => ['poll_option_id' => ['Please select a valid poll option.']],
                ], 422);
            }

            return back()->withErrors(['poll_option_id' => 'Please select a valid poll option.'])->withInput();
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message'           => 'Your vote has been submitted successfully.',
                'total_votes'       => $result['totalVotes'],
                'result_rows'       => $result['resultRows'],
                'has_already_voted' => true,
            ]);
        }

        return redirect()
            ->route('polls.show', $poll)
            ->with('success', 'Your vote has been submitted successfully.');
    }

    private function voteErrorResponse(Request $request, Poll $poll, string $message, int $status = 422): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $message], $status);
        }

        return redirect()->route('polls.show', $poll)->with('error', $message);
    }
}
