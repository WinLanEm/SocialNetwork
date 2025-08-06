<?php

use App\Domain\Chat\Entities\Chat;
use App\Domain\Message\Entities\Message;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use App\Domain\User\Entities\User;

class PaginateChatMessagesTest extends TestCase
{
    use DatabaseMigrations;


    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
        $this->artisan('mongo:indexes');
        DB::connection('mongodb')->getCollection('chats')->deleteMany([]);
    }

    #[Test]
    public function test_paginate_returns_200_and_empty_array_if_no_messages()
    {
        $user = User::factory()->create();
        $chat = Chat::factory()->create(['participants' => [$user->id]]);
        $res = $this->actingAs($user)
            ->get(route('get-chat-messages',[
            'chat_id' => $chat->id,
            'page' => 1
            ])
        );
        $res->assertOk();
        $res->assertJson([]);
    }

    #[Test]
    public function test_paginate_returns_messages_if_exist_in_chat()
    {
        $user = User::factory()->create();
        $chat = Chat::factory()->create(['participants' => [$user->id]]);
        $messages = Message::factory()
            ->count(3)
            ->forChat($chat->id)
            ->fromSender($chat->participants[0])
            ->withSecretKey($chat->secret_key)
            ->create();
        $res = $this->actingAs($user)
            ->get(route('get-chat-messages', [
            'chat_id' => $chat->id,
            'page' => 1,
        ]));
        $res->assertOk();
        $res->assertJsonCount(3, 'messages.data');
    }

    #[Test]
    public function test_paginate_returns_correct_structure()
    {
        $user = User::factory()->create();
        $chat = Chat::factory()->create(['participants' => [$user->id]]);
        $messages = Message::factory()
            ->count(3)
            ->forChat($chat->id)
            ->fromSender($chat->participants[0])
            ->withSecretKey($chat->secret_key)
            ->create();
        $res = $this->actingAs($user)
            ->get(route('get-chat-messages', [
            'chat_id' => $chat->id,
            'page' => 1,
        ]));
        $res->assertOk();
        $res->assertJsonStructure([
            'messages' => [
                'current_page',
                'data' => [
                    '*' => [
                        'content',
                        'updated_at',
                        'is_read',
                        'id',
                        'sender_id'
                    ],
                ],
                'last_page',
                'per_page',
                'total'
            ]
        ]);
    }

    #[Test]
    public function test_paginate_return_sort_by_updated_at_desc()
    {
        $user = User::factory()->create();
        $chat = Chat::factory()->create(['participants' => [$user->id]]);
        $messages = Message::factory()
            ->count(3)
            ->forChat($chat->id)
            ->fromSender($chat->participants[0])
            ->withSecretKey($chat->secret_key)
            ->create();
        $res = $this->actingAs($user)
            ->get(route('get-chat-messages', [
            'chat_id' => $chat->id,
            'page' => 1,
        ]));
        $res->assertOk();
        $responseData = $res->json('messages.data');
        $this->assertEquals(
            collect($responseData)->pluck('updated_at')->sortDesc()->values()->toArray(),
            collect($responseData)->pluck('updated_at')->toArray()
        );
    }
}


