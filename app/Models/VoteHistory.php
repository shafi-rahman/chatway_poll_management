<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VoteHistory extends Model
{
    protected $table = 'vote_history';

    protected $fillable = [
        'vote_id',
        'poll_id',
        'from_option_id',
        'to_option_id',
        'ip_address',
        'session_token',
    ];

    public function vote()
    {
        return $this->belongsTo(Vote::class);
    }

    public function poll()
    {
        return $this->belongsTo(Poll::class);
    }

    public function fromOption()
    {
        return $this->belongsTo(PollOption::class, 'from_option_id');
    }

    public function toOption()
    {
        return $this->belongsTo(PollOption::class, 'to_option_id');
    }
}
