<?php

namespace Database\Factories;

use Hash;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'avatar' => url('fake/avatar/文件' . mt_rand(1, 10) . '.jpg'),
            'sex' => mt_rand(1, 2),
            'email_verified_at' => now(),
            'password' => Hash::make('admin888'),
            'remember_token' => Str::random(10),
            'github' => fake()->url(),
            'email' => fake()->email(),
            'home' => fake()->url(),
            'weibo' => fake()->url(),
            'wechat' => fake()->url(),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     *
     * @return static
     */
    public function unverified()
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
