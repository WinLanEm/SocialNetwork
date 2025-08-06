<?php

use App\Domain\Chat\Entities\Chat;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use App\Domain\User\Entities\User;

class DestroyChatTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
        $this->artisan('mongo:indexes');
    }

    #[Test]
    public function a_user_can_be_destroy_chat()
    {
        DB::connection('mongodb')->getCollection('chats')->deleteMany([]);
        $this->artisan('mongo:indexes');
        $chat = Chat::factory()->create();
        $participantId = $chat->participants[0];
        $user = User::find($participantId);
        $res = $this
            ->withHeaders([
                'Accept' => 'application/json',
            ])
            ->actingAs($user)
            ->delete(route('destroy-chat', ['chat' => $chat->id]));
        $res->assertStatus(204);
    }

    #[Test]
    public function a_user_cant_destroy_not_exists_chat()
    {
        DB::connection('mongodb')->getCollection('chats')->deleteMany([]);
        $this->artisan('mongo:indexes');
        $user = User::factory()->create();
        $chat = Chat::factory()->create(['participants' => [$user->id]]);
        $res = $this
            ->actingAs($user)
            ->withHeaders([
                'Accept' => 'application/json',
            ])
            ->delete(route('destroy-chat', ['chat' => 'some_chat']));
        $res->assertStatus(404);
    }

    #[Test]
    public function unauthorized_user_cant_destroy_chat()
    {
        DB::connection('mongodb')->getCollection('chats')->deleteMany([]);
        $this->artisan('mongo:indexes');
        $chat = Chat::factory()->create();
        $res = $this
            ->withHeaders([
                'Accept' => 'application/json',
            ])
            ->delete(route('destroy-chat', ['chat' => $chat->id]));
        $res->assertStatus(403);
    }

    #[Test]
    public function test_user_cannot_delete_chat_they_are_not_participant_of()
    {
        DB::connection('mongodb')->getCollection('chats')->deleteMany([]);
        $this->artisan('mongo:indexes');
        $user = User::factory()->create();
        $participant = User::factory()->create();
        $chat = Chat::factory()->create(['participants' => [$participant->id]]);

        $res = $this
            ->actingAs($user)
            ->withHeaders([
                'Accept' => 'application/json',
            ])
            ->delete(route('destroy-chat', ['chat' => $chat->id]));
        $res->assertStatus(403);
    }
}
