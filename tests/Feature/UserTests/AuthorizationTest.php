<?php

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use App\Domain\User\Entities\User;

class AuthorizationTest extends TestCase
{
    use DatabaseMigrations;

    private array $registerData;
    private array $authorizationData;

    protected function setUp(): void
    {
        parent::setUp();
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
    public function a_user_can_be_authorized()
    {
        User::create($this->registerData);
        $res = $this->post('/authorize',[
            ...$this->authorizationData,
            '_token' => csrf_token(),
        ]);
        $res->assertStatus(302)
            ->assertRedirect(route('home'));
    }

    #[Test]
    public function a_user_cannot_be_authorized_with_incorrect_password()
    {
        User::create($this->registerData);
        $this->authorizationData['password'] = 'Password2';
        $res = $this->post('/authorize',$this->authorizationData);
        $res->assertStatus(302)
            ->assertSessionHasErrors('password');
    }

    #[Test]
    public function a_user_cannot_be_authorized_with_incorrect_phone()
    {
        User::create($this->registerData);
        $this->authorizationData['phone'] = '88888888888';
        $res = $this->post('/authorize',$this->authorizationData);
        $res->assertStatus(302)
            ->assertSessionHasErrors('password');
    }

    #[Test]
    public function after_authorize_user_is_authorized()
    {
        $user = User::create($this->registerData);
        $res = $this->post('/authorize',$this->authorizationData);
        $res->assertStatus(302);
        $this->assertAuthenticatedAs($user);
    }
}
