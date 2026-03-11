<?php

namespace App\Http\Controllers;

use App\Models\Poll;
use App\Models\Vote;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;
use App\Events\PollVoteUpdated; 
use Illuminate\Http\JsonResponse;


class PublicPollController extends Controller
{
    // show public poll page
    public function show(Request $request, Poll $poll): View
    {
        $poll->load(['options.votes', 'user']);

        [$hasStarted, $hasEnded, $isAvailableForVoting] = $this->getPollAvailability($poll);

        $sessionToken = $this->ensureSessionToken($request);

        $hasAlreadyVoted = Vote::query()
            ->where('poll_id', $poll->id)
            ->where(function ($query) use ($request, $sessionToken) {
                $query->where('ip_address', $request->ip())
                    ->orWhere('session_token', $sessionToken);
            })
            ->exists();

        $resultRows = $poll->resultRows();
        $totalVotes = $poll->totalVotesCount();

        return view('polls.show', [
            'poll' => $poll,
            'hasStarted' => $hasStarted,
            'hasEnded' => $hasEnded,
            'isAvailableForVoting' => $isAvailableForVoting,
            'hasAlreadyVoted' => $hasAlreadyVoted,
            'resultRows' => $resultRows,
            'totalVotes' => $totalVotes,
        ]);
    }

    // Store a vote
    public function vote(Request $request, Poll $poll): RedirectResponse|JsonResponse
    {
        [$hasStarted, $hasEnded, $isAvailableForVoting] = $this->getPollAvailability($poll);

        if (!$poll->is_active) {
            return $this->voteErrorResponse($request, $poll, 'This poll is currently inactive and not accepting votes.', 422);
        }

        if (!$hasStarted) {
            return $this->voteErrorResponse($request, $poll, 'This poll is not open yet.', 422);
        }

        if ($hasEnded) {
            return $this->voteErrorResponse($request, $poll, 'This poll has ended and is no longer accepting votes.', 422);
        }

        if (!$isAvailableForVoting) {
            return $this->voteErrorResponse($request, $poll, 'This poll is not currently available for voting.', 422);
        }

        $validated = $request->validate([
            'poll_option_id' => ['required', 'integer'],
        ]);

        $selectedOption = $poll->options()
            ->where('id', $validated['poll_option_id'])
            ->first();

        if (!$selectedOption) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Please select a valid poll option.',
                    'errors' => [
                        'poll_option_id' => ['Please select a valid poll option.'],
                    ],
                ], 422);
            }

            return back()
                ->withErrors([
                    'poll_option_id' => 'Please select a valid poll option.',
                ])
                ->withInput();
        }

        $sessionToken = $this->ensureSessionToken($request);
        $ipAddress = $request->ip();

        $hasAlreadyVoted = Vote::query()
            ->where('poll_id', $poll->id)
            ->where(function ($query) use ($ipAddress, $sessionToken) {
                $query->where('ip_address', $ipAddress)
                    ->orWhere('session_token', $sessionToken);
            })
            ->exists();

        if ($hasAlreadyVoted) {
            return $this->voteErrorResponse($request, $poll, 'You have already voted on this poll.', 422);
        }

        try {
            DB::transaction(function () use ($poll, $selectedOption, $ipAddress, $sessionToken): void {
                Vote::create([
                    'poll_id' => $poll->id,
                    'poll_option_id' => $selectedOption->id,
                    'ip_address' => $ipAddress,
                    'session_token' => $sessionToken,
                ]);
            });
        } catch (QueryException $exception) {
            return $this->voteErrorResponse($request, $poll, 'You have already voted on this poll.', 422);
        }

        $poll->load(['options.votes']);

        $resultRows = $poll->resultRows()->values()->all();
        $totalVotes = $poll->totalVotesCount();

        broadcast(new PollVoteUpdated(
            $poll,
            $resultRows,
            $totalVotes
        ));

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Your vote has been submitted successfully.',
                'total_votes' => $totalVotes,
                'result_rows' => $resultRows,
                'has_already_voted' => true,
            ]);
        }

        return redirect()
            ->route('polls.show', $poll)
            ->with('success', 'Your vote has been submitted successfully.');
    }

    // poll availability flags
    private function getPollAvailability(Poll $poll): array
    {
        $now = Carbon::now();

        $hasStarted = is_null($poll->starts_at) || $poll->starts_at->lte($now);
        $hasEnded = !is_null($poll->ends_at) && $poll->ends_at->lt($now);
        $isAvailableForVoting = $poll->is_active && $hasStarted && !$hasEnded;

        return [$hasStarted, $hasEnded, $isAvailableForVoting];
    }

    // stable session token for vote protection
    private function ensureSessionToken(Request $request): string
    {
        if (!$request->session()->has('poll_voter_token')) {
            $request->session()->put('poll_voter_token', (string) Str::uuid());
        }

        return (string) $request->session()->get('poll_voter_token');
    }

    // error response for voting issues
    private function voteErrorResponse(Request $request, Poll $poll, string $message, int $status = 422): RedirectResponse|JsonResponse 
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
            ], $status);
        }

        return redirect()
            ->route('polls.show', $poll)
            ->with('error', $message);
    }
}