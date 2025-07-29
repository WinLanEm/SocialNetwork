<?php

namespace Tests\Feature\UserTests;

use App\Domain\User\Entities\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use DatabaseMigrations;

    private array $userData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
        config(['scout.driver' => null]);
        $this->userData = [
            'username' => 'test_user_'.rand(1000, 9999),
            'phone' => '8'.rand(1000000000, 9999999999),
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
        ];
    }

    #[Test]
    public function returns_redirect_on_successful_registration()
    {
        $response = $this->post('/register', $this->userData);
        $response->assertRedirect(route('home'));
    }

    #[Test]
    public function creates_user_in_database()
    {
        $this->post('/register', $this->userData);

        $this->assertDatabaseHas('users', [
            'username' => $this->userData['username'],
            'phone' => $this->userData['phone'],
        ]);
    }

    #[Test]
    public function hashes_user_password()
    {
        $this->post('/register', $this->userData);

        $user = User::first();
        $this->assertTrue(
            Hash::check($this->userData['password'], $user->password)
        );
    }

    #[Test]
    public function register_require_valid_phone()
    {
        $this->userData['phone'] = 12345;
        $res = $this->post('/register', $this->userData);
        $res->assertStatus(302);
        $res->assertSessionHasErrors(['phone']);
    }

    #[Test]
    public function user_password_require_uppercase_character()
    {
        $this->userData['password'] = 'qwerty1234';
        $res = $this->post('/register', $this->userData);
        $res->assertStatus(302);
        $res->assertSessionHasErrors(['password']);
    }

    #[Test]
    public function user_password_require_eight_characters()
    {
        $this->userData['password'] = 'Qwerty';
        $res = $this->post('/register', $this->userData);
        $res->assertStatus(302);
        $res->assertSessionHasErrors(['password']);
    }

    #[Test]
    public function register_require_unique_username()
    {
        User::create($this->userData);
        $response = $this->post('/register', $this->userData);
        $response->assertSessionHasErrors(['username']);
    }

    #[Test]
    public function register_require_unique_phone()
    {
        User::create($this->userData);
        $response = $this->post('/register', $this->userData);
        $response->assertSessionHasErrors(['phone']);
    }

    #[Test]
    public function register_requires_password_confirmation()
    {
        $this->userData['password_confirmation'] = 'different';

        $this->post('/register', $this->userData)
            ->assertSessionHasErrors(['password_confirmation']);
    }
}
