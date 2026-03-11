<?php

namespace App\Http\Controllers;

use App\Models\Poll;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;

class PublicPollController extends Controller
{
    // show public poll page.
    public function show(Poll $poll): View
    {
        $poll->load(['options', 'user']);

        $now = Carbon::now();

        $hasStarted = is_null($poll->starts_at) || $poll->starts_at->lte($now);
        $hasEnded = !is_null($poll->ends_at) && $poll->ends_at->lt($now);

        $isAvailableForVoting = $poll->is_active && $hasStarted && !$hasEnded;

        return view('polls.show', [
            'poll' => $poll,
            'hasStarted' => $hasStarted,
            'hasEnded' => $hasEnded,
            'isAvailableForVoting' => $isAvailableForVoting,
        ]);
    }
}