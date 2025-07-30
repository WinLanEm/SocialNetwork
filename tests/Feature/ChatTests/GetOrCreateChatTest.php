<?php

use App\Domain\Chat\Entities\Chat;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use App\Domain\User\Entities\User;

class GetOrCreateChatTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
        $this->artisan('mongo:indexes');
    }

    #[Test]
    public function a_user_can_be_get_chat()
    {
        DB::connection('mongodb')->getCollection('chats')->deleteMany([]);
        $this->artisan('mongo:indexes');

        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $response = $this->actingAs($user1)
            ->postJson(route('get-or-create-chat'), [
                'participants' => [$user2->id],
                'type' => 'private',
            ]);

        $response->assertStatus(200);

        $response = $this->actingAs($user1)
            ->postJson(route('get-or-create-chat'), [
                'participants' => [$user2->id],
                'type' => 'private',
            ]);

        $response->assertStatus(200);

        $this->assertEquals(1, Chat::count());
    }

    #[Test]
    public function unauthorized_user_cannot_get_chat()
    {
        DB::connection('mongodb')->getCollection('chats')->deleteMany([]);
        $this->artisan('mongo:indexes');
        $chat = Chat::factory()->create();
        $res = $this
            ->postJson(route('get-or-create-chat'),[
                'participants' => [$chat->participants[0]],
                'type' => $chat->type,
            ]);
        $res->assertStatus(500);
    }

    #[Test]
    public function a_user_can_create_a_chat()
    {
        DB::connection('mongodb')->getCollection('chats')->deleteMany([]);
        $this->artisan('mongo:indexes');
        $user = User::factory()->create();
        $participant = User::factory()->create();
        $res = $this->actingAs($user)
            ->postJson(route('get-or-create-chat'),[
                'participants' => [$participant->id],
                'type' => 'private',
            ]);
        $res->assertOk();
        $this->assertEquals(1, Chat::count());
    }
    #[Test]
    public function a_unauthorized_user_cannot_create_a_chat()
    {
        DB::connection('mongodb')->getCollection('chats')->deleteMany([]);
        $this->artisan('mongo:indexes');
        $user = User::factory()->create();
        $participant = User::factory()->create();
        $res = $this
            ->postJson(route('get-or-create-chat'),[
                'participants' => [$participant->id],
                'type' => 'private',
            ]);
        $res->assertStatus(500);
        $this->assertEquals(0, Chat::count());
    }
}
