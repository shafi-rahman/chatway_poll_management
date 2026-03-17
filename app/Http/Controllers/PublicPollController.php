<?php

namespace App\Http\Controllers;

use App\Http\Requests\VoteRequest;
use App\Models\Poll;
use App\Services\VoteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class PublicPollController extends Controller
{
    // Cookie name
    private const VOTER_COOKIE = 'poll_voter_token';

    // 1 year
    private const COOKIE_MINUTES = 60 * 24 * 365;

    public function __construct(private VoteService $voteService) {}

    public function show(Request $request, Poll $poll): Response
    {
        $cookieToken = $this->resolveCookieToken($request);

        $data = $this->voteService->getPollData($poll, $cookieToken);

        return response()
            ->view('polls.show', $data)
            ->cookie(self::VOTER_COOKIE, $cookieToken, self::COOKIE_MINUTES);
    }

    public function vote(VoteRequest $request, Poll $poll): RedirectResponse|JsonResponse
    {
        [$hasStarted, $hasEnded] = $this->voteService->getPollAvailability($poll);

        if (!$poll->is_active) {
            return $this->voteErrorResponse($request, $poll, 'This poll is currently inactive and not accepting votes.');
        }

        if (!$hasStarted) {
            return $this->voteErrorResponse($request, $poll, 'This poll is not open yet.');
        }

        if ($hasEnded) {
            return $this->voteErrorResponse($request, $poll, 'This poll has ended and is no longer accepting votes.');
        }

        $cookieToken = $this->resolveCookieToken($request);
        $ipAddress   = $request->ip();

        try {
            $result = $this->voteService->submitVote($poll, $request->integer('poll_option_id'), $ipAddress, $cookieToken);
        } catch (\Throwable) {
            return $this->voteErrorResponse($request, $poll, 'Something went wrong. Please try again later.', 500);
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

        if ($result === false) {
            return $this->voteErrorResponse($request, $poll, 'You have already voted on this poll.');
        }

        $successMessage = $result['isUpdate']
            ? 'Your vote has been updated successfully.'
            : 'Your vote has been submitted successfully.';

        $cookie = cookie(self::VOTER_COOKIE, $cookieToken, self::COOKIE_MINUTES);

        if ($request->expectsJson()) {
            return response()->json([
                'message'     => $successMessage,
                'total_votes' => $result['totalVotes'],
                'result_rows' => $result['resultRows'],
                'is_update'   => $result['isUpdate'],
            ])->cookie($cookie);
        }

        return redirect()
            ->route('polls.show', $poll)
            ->with('success', $successMessage)
            ->cookie($cookie);
    }

    private function resolveCookieToken(Request $request): string
    {
        return $request->cookie(self::VOTER_COOKIE) ?? (string) Str::uuid();
    }

    private function voteErrorResponse(Request $request, Poll $poll, string $message, int $status = 422): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $message], $status);
        }

        return redirect()->route('polls.show', $poll)->with('error', $message);
    }
}
