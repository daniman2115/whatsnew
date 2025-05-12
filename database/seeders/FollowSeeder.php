<?php

namespace Database\Seeders;

use App\Models\Follow;
use App\Models\User;
use Illuminate\Database\Seeder;

class FollowSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();

        foreach ($users as $user) {
            // Pick random users to follow (excluding self)
            $following = $users->where('id', '!=', $user->id)->random(rand(3, 10));

            foreach ($following as $followedUser) {
                // Use Eloquent model instead of DB facade
                Follow::firstOrCreate([
                    'follower_id' => $user->id,
                    'following_id' => $followedUser->id,
                ]);
            }
        }

        // Recalculate counts using relationships
        foreach ($users as $user) {
            $user->update([
                'followers_count' => $user->followers()->count(),
                'following_count' => $user->following()->count(),
            ]);
        }
    }
}