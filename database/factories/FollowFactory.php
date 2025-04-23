<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Follow>
 */
class FollowFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $follower = User::inRandomOrder()->first();
        $following = User::where('id', '!=', $follower->id)->inRandomOrder()->first();

        return [
            'follower_id' => $follower->id,
            'following_id' => $following->id,
            'created_at' => now(),
        ];
    }
}
