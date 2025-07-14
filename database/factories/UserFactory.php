<?php

namespace Database\Factories;

use App\Domain\User\Entities\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\User\Entities\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected $model = User::class;
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'username' => fake()->unique()->userName(),
            'phone' => fake()->unique()->phoneNumber(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
    protected function generateUniqueUsername()
    {
        $username = fake()->userName;

        while (User::withTrashed()->where('username', $username)->exists()) {
            $username = fake()->userName;
        }

        return $username;
    }

    protected function generateUniquePhone()
    {
        $phone = fake()->phoneNumber;

        while (User::withTrashed()->where('phone', $phone)->exists()) {
            $phone = fake()->phoneNumber;
        }

        return $phone;
    }
}
