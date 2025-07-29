<?php

use Illuminate\Foundation\Testing\DatabaseMigrations;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use App\Domain\User\Entities\User;

class SearchUsersTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
        $this->artisan('elasticsearch:create-index users --force');
    }


    #[Test]
    public function a_user_can_be_searched()
    {
        $authUser = User::factory()->create();
        $searchableUser = User::factory()->create(['username' => 'testuser']);

        User::makeAllSearchable(); // Это ключевая строка!

        sleep(1);

        $response = $this->actingAs($authUser)
            ->get('/search/users?username=testuser');

        $response->assertOk();
        $response->assertJsonFragment([
            'username' => 'testuser'
        ]);
    }

    #[Test]
    public function a_user_can_be_search_undefined_users()
    {
        $authUser = User::factory()->create();

        User::makeAllSearchable();

        sleep(1);

        $response = $this->actingAs($authUser)
            ->get('/search/users?username=testuser');

        $response->assertOk();
        $response->assertJson([]);
    }

    #[Test]
    public function a_user_can_be_search_many_users()
    {
        $authUser = User::factory()->create();
        $searchableUser = User::factory()->create(['username' => 'testuser']);
        $searchableUser = User::factory()->create(['username' => 'testuseq']);

        User::makeAllSearchable();
        sleep(1);
        $response = $this->actingAs($authUser)
            ->get('/search/users?username=tes');

        $response->assertOk();
        $response->assertJsonCount(2);

        $response->assertJsonFragment(['username' => 'testuser']);
        $response->assertJsonFragment(['username' => 'testuseq']);
    }

    #[Test]
    public function search_returns_correct_users()
    {
        $authUser = User::factory()->create();
        $searchableUser = User::factory()->create(['username' => 'testuser']);
        $searchableUser = User::factory()->create(['username' => 'testuseq']);
        $searchableUser = User::factory()->create(['username' => 'fsastuseq']);

        User::makeAllSearchable();
        sleep(1);
        $response = $this->actingAs($authUser)
            ->get('/search/users?username=tes');

        $response->assertOk();
        $response->assertJsonCount(2);

        $response->assertJsonFragment(['username' => 'testuser']);
        $response->assertJsonFragment(['username' => 'testuseq']);

        $responseJsom = $response->json();
        $this->assertNotContains('fsastuseq', array_column($responseJsom, 'username'));
    }
}
