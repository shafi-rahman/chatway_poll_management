<?php

namespace Tests\Feature;

use App\Models\Poll;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PollManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_a_poll_with_multiple_options(): void
    {
        $admin = User::factory()->create([
            'role' => User::ADMIN,
        ]);

        $question = 'Which feature should we build next in Chatway?';

        $response = $this->actingAs($admin)->post(route('admin.polls.store'), [
            'question' => $question,
            'is_active' => 1,
            'starts_at' => null,
            'ends_at' => null,
            'options' => [
                'Talk Mode',
                'Export Reports',
                'Team Chat',
            ],
        ]);

        $response->assertRedirect(route('admin.polls.index'));
        $response->assertSessionHas('success', 'Poll created successfully.');

        $this->assertDatabaseHas('polls', [
            'user_id' => $admin->id,
            'question' => $question,
        ]);

        $poll = Poll::first();

        $this->assertNotNull($poll);
        $this->assertCount(3, $poll->options);
    }
}