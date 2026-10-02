<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'fullname' => fake()->name(),
            'username' => fake()->unique()->userName(),
            'email' => fake()->unique()->safeEmail(),
            'phone_number' => fake()->unique()->numerify('03#########'),
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
            'password' => 'password',
            'is_buyer' => true,
            'is_seller' => false,
            'timezone' => 'UTC',
            'remember_token' => Str::random(10),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
            'phone_verified_at' => null,
        ]);
    }

    public function provider(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_seller' => true,
            'is_buyer' => true,
        ]);
    }

    public function seller(): static
    {
        return $this->provider();
    }
}
