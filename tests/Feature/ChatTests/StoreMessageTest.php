<?php

namespace Tests\Feature\ChatTests;

use App\Application\ChatUnreadMessage\Jobs\StoreUnreadMessagesCountJob;
use App\Application\Message\DTOs\StoreMessageDTO;
use App\Application\Message\Events\FailStoreMessageEvent;
use App\Application\Message\Events\SendRealMessageIdEvent;
use App\Application\Message\Events\StoreMessageEvent;
use App\Application\Message\Jobs\StoreMessageJob;
use App\Domain\Chat\Actions\ChatRenderActionInterface;
use App\Domain\Chat\Entities\Chat;
use App\Domain\Chat\Repositories\GetChatByIdRepositoryInterface;
use App\Domain\Message\Actions\MessageCryptoActionInterface;
use App\Domain\Message\Actions\UpdateLastMessageActionInterface;
use App\Domain\User\Entities\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StoreMessageTest extends TestCase
{
//    use DatabaseMigrations;
//    protected function setUp(): void
//    {
//        parent::setUp();
//    }
//
//    #[Test]
//    public function it_stores_message_and_triggers_events()
//    {
//        // Arrange
//        $user = User::factory()->create();
//        $recipient = User::factory()->create();
//        $chat = Chat::factory()->create([
//            'participants' => [$user->id, $recipient->id],
//            'secret_key' => 'test_key'
//        ]);
//
//        $dto = new StoreMessageDTO($chat->id, 'Test content', 'temp123');
//
//
//        $cryptoMock = $this->mock(MessageCryptoActionInterface::class);
//        $cryptoMock->shouldReceive('encrypt')
//            ->with('test_key', 'Test content')
//            ->andReturn('encrypted_content');
//
//        $renderMock = $this->mock(ChatRenderActionInterface::class);
//        $renderMock->shouldReceive('renderForPrivateChat');
//
//        $lastMessageMock = $this->mock(UpdateLastMessageActionInterface::class);
//        $lastMessageMock->shouldReceive('update');
//
//        Event::fake();
//
//        // Act
//        $job = new StoreMessageJob($dto, $user->id);
//        $job->handle(
//            app()->make(GetChatByIdRepositoryInterface::class),
//            $cryptoMock,
//            $renderMock,
//            $lastMessageMock
//        );
//
//        // Assert
//        $this->assertDatabaseHas('messages', [
//            'chat_id' => $chat->id,
//            'sender_id' => $user->id,
//            'content' => 'encrypted_content'
//        ]);
//
//        Event::assertDispatched(SendRealMessageIdEvent::class);
//        Event::assertDispatched(StoreMessageEvent::class);
//        Event::assertNotDispatched(FailStoreMessageEvent::class);
//
//        Bus::assertDispatched(StoreUnreadMessagesCountJob::class);
//    }
}
