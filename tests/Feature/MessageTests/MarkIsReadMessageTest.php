<?php

use App\Application\Chat\Events\MakeChatIsReadEvent;
use App\Application\Message\Events\MakeMessagesIsReadEvent;
use App\Application\Message\Jobs\MakeMessagesIsReadJob;
use App\Domain\Chat\Entities\Chat;
use App\Domain\Message\Entities\Message;
use App\Domain\Message\Repositories\DecrementUnreadMessagesRepositoryInterface;
use App\Domain\Message\Repositories\MakeMessagesIsReadRepositoryInterface;
use App\Domain\Message\Repositories\ReadMessageByIdRepositoryInterface;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use App\Domain\User\Entities\User;

class MarkIsReadMessageTest extends TestCase
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
    public function a_user_can_be_mark_is_read_message()
    {
        DB::connection('mongodb')->getCollection('chats')->deleteMany([]);
        $this->artisan('mongo:indexes');

        $chat = Chat::factory()->create();
        $user = User::find($chat->participants[0]);
        $message = Message::factory()
            ->forChat($chat->id)
            ->fromSender($user->id)
            ->withSecretKey($chat->secret_key)
            ->create(['is_read' => false]);


        Event::fake();

        $response = $this
            ->actingAs($user)
            ->postJson(route('mark-messages-as-read'), [
                'message_ids' => [$message->id],
                'user_id' => $user->id,
                'chat_id' => $chat->id,
            ]);

        $response->assertNoContent();

        $this->assertTrue(Message::find($message->id)->is_read);

        Event::assertDispatched(MakeMessagesIsReadEvent::class, function ($event) use ($message, $chat) {
            return $event->broadcastWith()['message_ids'] == [$message->id]
                && $event->broadcastWith()['chat_id'] == $chat->id;
        });

        Event::assertDispatched(MakeChatIsReadEvent::class);
    }

    #[Test]
    public function invalid_request_fails_before_dispatching_job()
    {
        DB::connection('mongodb')->getCollection('chats')->deleteMany([]);
        $this->artisan('mongo:indexes');
        Bus::fake();

        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson(route('mark-messages-as-read'), []);

        $response->assertStatus(422);
        Bus::assertNotDispatched(MakeMessagesIsReadJob::class);

        $response = $this->actingAs($user)
            ->postJson(route('mark-messages-as-read'), [
                'message_ids' => 'not-an-array',
                'user_id' => 'invalid',
                'chat_id' => 123,
            ]);

        $response->assertStatus(422);
        Bus::assertNotDispatched(MakeMessagesIsReadJob::class);
        DB::connection('mongodb')->getCollection('chats')->deleteMany([]);
    }

    #[Test]
    public function does_not_duplicate_events_for_already_read_messages()
    {
        DB::connection('mongodb')->getCollection('chats')->deleteMany([]);
        $this->artisan('mongo:indexes');
        $chat = Chat::factory()->create();
        $user = User::find($chat->participants[0]);
        $message = Message::factory()
            ->forChat($chat->id)
            ->fromSender($user->id)
            ->withSecretKey($chat->secret_key)
            ->create(['is_read' => false]);
        Event::fake();

        $this->actingAs($user)
            ->postJson(route('mark-messages-as-read'), [
                'message_ids' => [$message->id],
                'user_id' => $user->id,
                'chat_id' => $chat->id,
            ]);

        $this->actingAs($user)
            ->postJson(route('mark-messages-as-read'), [
                'message_ids' => [$message->id],
                'user_id' => $user->id,
                'chat_id' => $chat->id,
            ]);

        Event::assertDispatched(MakeMessagesIsReadEvent::class, 1);
        DB::connection('mongodb')->getCollection('chats')->deleteMany([]);
    }
}
