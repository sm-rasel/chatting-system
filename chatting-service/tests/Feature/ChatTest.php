<?php

namespace Tests\Feature;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ChatTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_search_other_users(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create(['name' => 'Test User']);

        $response = $this->actingAs($user)->getJson('/api/users/search?q=Test');

        $response->assertStatus(200)->assertJsonFragment(['name' => 'Test User']);
    }

    public function test_user_can_start_conversation(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $response = $this->actingAs($user)->getJson('/api/conversations', [
            'user_id' => $otherUser->id,
        ]);
        $response->assertStatus(200)->assertDatabaseHas('conversations', [
            'user_one_id' => $user->id,
            'user_two_id' => $otherUser->id,
        ]);
    }


    public function test_user_can_send_message(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $conversation = Conversation::create([
            'user_one_id' => $user->id,
            'user_two_id' => $otherUser->id,
        ]);
        $response = $this->actingAs($user)->postJson("/api/conversations/{$conversation->id}/messages", [
            'message' => 'Hello!',
        ]);
        $response->assertStatus(200);
        $this->assertDatabaseHas('chats', [
            'conversation_id' => $conversation->id,
            'message' => 'Hello!',
        ]);
    }
}
