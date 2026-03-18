<?php

namespace App\Policies;

use App\Models\Poll;
use App\Models\User;

class PollPolicy
{
    public function view(User $user, Poll $poll): bool
    {
        return $user->id === $poll->user_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Poll $poll): bool
    {
        return $user->id === $poll->user_id;
    }

    public function delete(User $user, Poll $poll): bool
    {
        return $user->id === $poll->user_id;
    }
}
