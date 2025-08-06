<?php

namespace Tests\Feature\MessageTests;

use App\Application\ChatUnreadMessage\Jobs\StoreUnreadMessagesCountJob;
use App\Application\ChatUnreadMessage\Repositories\StoreUnreadMessagesCountRepository;
use App\Application\Message\DTOs\StoreMessageDTO;
use App\Application\Message\Events\SendRealMessageIdEvent;
use App\Application\Message\Events\StoreMessageEvent;
use App\Application\Message\Jobs\StoreMessageJob;
use App\Domain\Chat\Actions\ChatRenderActionInterface;
use App\Domain\Chat\Entities\Chat;
use App\Domain\Chat\Repositories\GetChatByIdRepositoryInterface;
use App\Domain\ChatUnreadMessage\Entities\ChatUnreadMessage;
use App\Domain\ChatUnreadMessage\Repositories\StoreUnreadMessagesCountRepositoryInterface;
use App\Domain\Message\Actions\MessageCryptoActionInterface;
use App\Domain\Message\Actions\UpdateLastMessageActionInterface;
use App\Domain\Message\Entities\Message;
use App\Domain\User\Entities\User;
use App\Infrastructure\User\Repositories\SearchUserRepository;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StoreMessageTest extends TestCase
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
    public function send_message_requires_all_fields()
    {
        $response = $this->postJson(route('send-message'), []);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['content', 'chat_id', 'temp_id']);
    }

    #[Test]
    public function send_message_requires_proper_field_types()
    {
        $response = $this->postJson(route('send-message'), [
            'content' => 12345,
            'chat_id' => false,
            'temp_id' => [],
        ]);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['content', 'chat_id', 'temp_id']);
    }

    #[Test]
    public function content_cannot_exceed_max_length()
    {
        $longContent = str_repeat('a', 65536);
        $response = $this->postJson(route('send-message'), [
            'content' => $longContent,
            'chat_id' => 'valid_chat_id',
            'temp_id' => 'valid_temp_id',
        ]);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['content']);
    }

    #[Test]
    public function temp_id_is_required()
    {
        $response = $this->postJson(route('send-message'), [
            'content' => 'Valid content',
            'chat_id' => 'valid_chat_id',
        ]);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['temp_id']);
    }

    #[Test]
    public function can_send_message_with_valid_data()
    {
        $user = User::factory()->create();
        $chat = Chat::factory()->create(['participants' => [$user->id]]);

        $response = $this->actingAs($user)
            ->postJson(route('send-message'), [
                'content' => 'Valid message content',
                'chat_id' => $chat->id,
                'temp_id' => 'unique-temp-id-123',
            ]);

        $response->assertStatus(201);
    }

    #[Test]
    public function a_user_can_create_a_real_message()
    {
        Event::fake();

        DB::connection('mongodb')->table('messages')->truncate();

        $user = User::factory()->create();
        $chat = Chat::factory()->create(['participants' => [$user->id]]);

        $tempId = 'unique-temp-id-123';
        $response = $this->actingAs($user)
            ->postJson(route('send-message'), [
                'content' => 'Valid message content',
                'chat_id' => $chat->id,
                'temp_id' => $tempId,
            ]);

        $response->assertStatus(201);

        Event::assertDispatched(SendRealMessageIdEvent::class, function ($event) use ($tempId) {
            return $event->broadcastWith()['temp_id'] == $tempId
                && $event->broadcastWith()['message_id'];
        });

        Event::assertDispatched(SendRealMessageIdEvent::class, 1);

        $message = Message::where('chat_id', $chat->id)
            ->where('sender_id', $user->id)
            ->first();

        $this->assertNotNull($message);
    }

    #[Test]
    public function store_message_trigger_store_message_event()
    {
        Event::fake();

        DB::connection('mongodb')->table('messages')->truncate();

        $user = User::factory()->create();
        $chat = Chat::factory()->create(['participants' => [$user->id]]);

        $tempId = 'unique-temp-id-123';
        $response = $this->actingAs($user)
            ->postJson(route('send-message'), [
                'content' => 'Valid message content',
                'chat_id' => $chat->id,
                'temp_id' => $tempId,
            ]);

        $response->assertStatus(201);

        Event::assertDispatched(StoreMessageEvent::class, function ($event) use ($chat,$user) {
            return $event->broadcastWith()['sender_id'] == $user->id
                && $event->broadcastWith()['chat_id'] == $chat->id;
        });

        Event::assertDispatched(StoreMessageEvent::class, 1);
    }
    #[Test]
    public function store_unread_messages_count_job_handles_repository_correctly()
    {
        // Подготовка
        $recipient1 = User::factory()->create();
        $recipient2 = User::factory()->create();
        $sender = User::factory()->create();
        $chatId = '507f1f77bcf86cd799439011';

        $mockRepository = Mockery::mock(StoreUnreadMessagesCountRepositoryInterface::class);
        $mockRepository->shouldReceive('exec')
            ->with($recipient1->id, $sender->id, $chatId)
            ->once()
            ->andReturn(true);

        $mockRepository->shouldReceive('exec')
            ->with($recipient2->id, $sender->id, $chatId)
            ->once()
            ->andReturn(true);

        // Создаем job с 2 участниками
        $job = new StoreUnreadMessagesCountJob(
            [$recipient1->id, $recipient2->id],
            $sender->id,
            $chatId
        );

        // Запускаем job с моком репозитория
        $job->handle($mockRepository);

        // Проверки выполняются через shouldReceive в моке
    }

    #[Test]
    public function store_message_job_dispatches_unread_count_job_correctly_for_private_chat()
    {
        Bus::fake();
        Event::fake();

        $sender = User::factory()->create();
        $recipient = User::factory()->create();

        $chat = Chat::factory()->create([
            'participants' => [$sender->id, $recipient->id],
            'type' => 'private'
        ]);

        $dto = new StoreMessageDTO(
            content: 'Test message',
            chatId: $chat->id,
            tempId: 'temp-123'
        );

        // Мокаем зависимости
        $mockChatRepo = Mockery::mock(GetChatByIdRepositoryInterface::class);
        $mockChatRepo->shouldReceive('exec')
            ->with($chat->id)
            ->andReturn($chat);

        $mockCrypto = Mockery::mock(MessageCryptoActionInterface::class);
        $mockCrypto->shouldReceive('encrypt')
            ->andReturn('encrypted-content');

        $mockRender = Mockery::mock(ChatRenderActionInterface::class);
        $mockRender->shouldReceive('renderForPrivateChat');

        $mockLastMessage = Mockery::mock(UpdateLastMessageActionInterface::class);
        $mockLastMessage->shouldReceive('update');

        // Создаем и запускаем job
        $job = new StoreMessageJob($dto, $sender->id);
        $job->handle(
            $mockChatRepo,
            $mockCrypto,
            $mockRender,
            $mockLastMessage
        );

        Bus::assertDispatched(StoreUnreadMessagesCountJob::class, function ($job) use ($recipient, $sender, $chat) {
            return count($job->getChatMembersIds()) === 1
                && in_array($recipient->id, $job->getChatMembersIds())
                && !in_array($sender->id, $job->getChatMembersIds())
                && $job->getSenderId() === $sender->id
                && $job->getChatId() === $chat->id;
        });
    }

    #[Test]
    public function store_unread_messages_count_repository_works_correctly()
    {
        // Подготовка
        DB::connection('mongodb')->getCollection('chat_unread_messages')->deleteMany([]);

        $recipient = User::factory()->create();
        $sender = User::factory()->create();
        $chat = Chat::factory()->create([
            'participants' => [$sender->id, $recipient->id],
            'type' => 'private'
        ]);
        $chatId = $chat->id;

        $repository = new StoreUnreadMessagesCountRepository();

        $result = $repository->exec($recipient->id, $sender->id, $chatId);
        $this->assertTrue($result);

        $record = ChatUnreadMessage::where('chat_id', $chatId)
            ->where('recipient_id', $recipient->id)
            ->where('sender_id', $sender->id)
            ->first();

        $this->assertNotNull($record);
        $this->assertEquals(1, $record['unread_count']);

        $result = $repository->exec($recipient->id, $sender->id, $chatId);
        $this->assertTrue($result);

        $updatedRecord = ChatUnreadMessage::where('chat_id', $chatId)
            ->where('recipient_id', $recipient->id)
            ->where('sender_id', $sender->id)
            ->first();

        $this->assertEquals(2, $updatedRecord['unread_count']);
    }
}
