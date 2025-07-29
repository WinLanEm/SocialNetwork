<?php

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\Testing\File;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use App\Domain\User\Entities\User;

class UpdateUserDataTest extends TestCase
{
    use DatabaseMigrations;

    private array $registerData;

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
    }

    #[Test]
    public function user_can_be_update_full_profile_data(): void
    {
        $user = User::create($this->registerData);
        Storage::fake('public');
        $userAvatar = File::create('avatar.jpg');

        $res = $this->actingAs($user)
            ->patch(route('update-profile'), [
                'username' => 'new_username',
                'phone' => '88888888888',
                'bio' => 'my bio',
                'avatar_url' => $userAvatar,
            ]);

        $res->assertOk();
        $user->refresh();
        $res->assertJson([
            'username' => 'new_username',
            'phone' => '88888888888',
            'bio' => 'my bio',
            'avatar_url' => $user->avatar_url,
        ]);
    }

    #[Test]
    public function user_can_update_avatar_only(): void
    {
        $user = User::create($this->registerData);
        Storage::fake('public');
        $userAvatar = File::create('avatar.jpg');

        $res = $this->actingAs($user)
            ->patch(route('update-profile'), [
                'avatar_url' => $userAvatar,
            ]);
        $res->assertOk();
        $user->refresh();
        $res->assertJson([
            'username' => $user->username,
            'phone' => $user->phone,
            'bio' => $user->bio,
            'avatar_url' => $user->avatar_url,
        ]);
    }

    #[Test]
    public function on_update_avatar_it_save_to_storage(): void
    {
        $user = User::create($this->registerData);
        Storage::fake('public');
        $userAvatar = File::create('avatar.jpg');

        $res = $this->actingAs($user)
            ->patch(route('update-profile'), [
                'avatar_url' => $userAvatar,
            ]);
        $res->assertOk();
        $user->refresh();
        $this->assertTrue(
            Storage::disk('public')->exists(
                str_replace(Storage::disk('public')->url(''), '', $user->avatar_url)
            )
        );
    }

    #[Test]
    public function updated_avatar_url_required_type_is_image(): void
    {
        $user = User::create($this->registerData);
        Storage::fake('public');
        $userAvatar = File::create('avatar');

        $res = $this->actingAs($user)
            ->patch(route('update-profile'), [
                'avatar_url' => $userAvatar,
            ]);

        $res->assertStatus(302)
        ->assertSessionHasErrors('avatar_url');
    }

    #[Test]
    public function a_user_can_be_update_last_seen(): void
    {
        $user = User::create($this->registerData);
        $res = $this->actingAs($user)
            ->patch(route('update-profile'), [
                'last_seen' => fake()->date(),
            ]);
        $res->assertOk();
    }
}
