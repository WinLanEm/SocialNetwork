<?php

use Illuminate\Foundation\Testing\DatabaseMigrations;
use PHPUnit\Framework\Attributes\Test;
use App\Domain\User\Entities\User;
use Tests\TestCase;

class LogoutTest extends TestCase
{
    use DatabaseMigrations;

    private array $registerData;
    private array $authorizationData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->withoutMiddleware();
        config(['scout.driver' => null]);
        $this->registerData = [
            'username' => 'user',
            'password' => 'Password1',
            'password_confirmation' => 'Password1',
            'phone' => '89999999999',
        ];
        $this->authorizationData = [
            'password' => 'Password1',
            'phone' => '89999999999',
        ];
    }


    #[Test]
    public function a_user_can_be_logout()
    {
        $user = User::create($this->registerData);
        $this->actingAs($user);
        $res = $this->post('/logout');
        $res->assertStatus(200);
        $this->assertGuest();
    }

    #[Test]
    public function a_user_cant_logout_without_login()
    {
        User::create($this->registerData);
        $res = $this->post('/logout');
        $res->assertInertia(function ($page) {
            $page->where('logout_error', 'You not authorized');
        });
        $this->assertFalse(auth()->check());
    }
};
