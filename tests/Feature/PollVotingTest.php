<?php

namespace Tests\Feature;

use App\Models\Poll;
use App\Models\PollOption;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PollVotingTest extends TestCase
{
    use RefreshDatabase;

    private function createPollWithOptions(): Poll
    {
        $admin = User::factory()->create([
            'role' => User::ADMIN,
        ]);

        $poll = Poll::create([
            'user_id' => $admin->id,
            'question' => 'Which feature should we build next?',
            'is_active' => true,
            'starts_at' => null,
            'ends_at' => null,
        ]);

        PollOption::create([
            'poll_id' => $poll->id,
            'option_text' => 'Dark Mode',
            'sort_order' => 1,
        ]);

        PollOption::create([
            'poll_id' => $poll->id,
            'option_text' => 'Export Reports',
            'sort_order' => 2,
        ]);

        return $poll->fresh('options');
    }

    public function test_guest_user_can_submit_a_vote_successfully(): void
    {
        $poll = $this->createPollWithOptions();
        $option = $poll->options->first();

        $response = $this->withSession([
                'poll_voter_token' => 'vote-session-1',
            ])->withServerVariables([
                'REMOTE_ADDR' => '127.0.0.10',
            ])->postJson(route('polls.vote', $poll), [
                'poll_option_id' => $option->id,
        ]);

        $response->assertOk()
            ->assertJson([
                'message' => 'Your vote has been submitted successfully.',
                'has_already_voted' => true,
                'total_votes' => 1,
            ]);

        $this->assertDatabaseHas('polls', [
            'id' => $poll->id,
            'total_votes' => 1,
        ]);

        $this->assertDatabaseHas('poll_options', [
            'id' => $option->id,
            'vote_count' => 1,
        ]);

        $this->assertDatabaseHas('votes', [
            'poll_id' => $poll->id,
            'poll_option_id' => $option->id,
            'ip_address' => '127.0.0.10',
            'session_token' => 'vote-session-1',
        ]);
    }

    public function test_vote_submission_updates_poll_and_option_counters(): void
    {
        $poll = $this->createPollWithOptions();
        $option = $poll->options->first();

        $this->withSession([
                'poll_voter_token' => 'counter-session',
            ])
            ->withServerVariables([
                'REMOTE_ADDR' => '127.0.0.90',
            ])
            ->postJson(route('polls.vote', $poll), [
                'poll_option_id' => $option->id,
            ])
            ->assertOk();

        $this->assertDatabaseHas('polls', [
            'id' => $poll->id,
            'total_votes' => 1,
        ]);

        $this->assertDatabaseHas('poll_options', [
            'id' => $option->id,
            'vote_count' => 1,
        ]);
    }

    public function test_same_user_cannot_vote_more_than_once(): void
    {
        $poll = $this->createPollWithOptions();
        $option = $poll->options->first();

        $payload = [
            'poll_option_id' => $option->id,
        ];

        $this->withSession([
                'poll_voter_token' => 'duplicate-session',
            ])
            ->withServerVariables([
                'REMOTE_ADDR' => '127.0.0.20',
            ])
            ->postJson(route('polls.vote', $poll), $payload)
            ->assertOk();

        $secondResponse = $this->withSession([
                'poll_voter_token' => 'duplicate-session',
            ])
            ->withServerVariables([
                'REMOTE_ADDR' => '127.0.0.20',
            ])
            ->postJson(route('polls.vote', $poll), $payload);

        $secondResponse->assertStatus(422)
            ->assertJson([
                'message' => 'You have already voted on this poll.',
            ]);

        $this->assertDatabaseCount('votes', 1);
    }

    public function test_vote_must_be_for_a_valid_option_in_the_poll(): void
    {
        $poll = $this->createPollWithOptions();

        $otherAdmin = User::factory()->create([
            'role' => User::ADMIN,
        ]);

        $otherPoll = Poll::create([
            'user_id' => $otherAdmin->id,
            'question' => 'Another poll',
            'is_active' => true,
            'starts_at' => null,
            'ends_at' => null,
        ]);

        $foreignOption = PollOption::create([
            'poll_id' => $otherPoll->id,
            'option_text' => 'Foreign Option',
            'sort_order' => 1,
        ]);

        $response = $this->withSession([
                'poll_voter_token' => 'invalid-option-session',
            ])
            ->withServerVariables([
                'REMOTE_ADDR' => '127.0.0.30',
            ])
            ->postJson(route('polls.vote', $poll), [
                'poll_option_id' => $foreignOption->id,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('poll_option_id');

        $this->assertDatabaseCount('votes', 0);
    }
}