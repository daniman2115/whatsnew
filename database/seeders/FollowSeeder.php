<?php

namespace Database\Seeders;

use App\Models\Follow;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FollowSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        foreach ($users as $user) {
            // Pick random users to follow (excluding self)
            $following = $users->where('id', '!=', $user->id)->random(rand(3, 10));

            foreach ($following as $followedUser) {
                DB::table('follows')->updateOrInsert([
                    'follower_id' => $user->id,
                    'following_id' => $followedUser->id,
                ]);
            }
        }

        // ✅ Recalculate follower/following counts
        foreach ($users as $user) {
            $followersCount = DB::table('follows')->where('following_id', $user->id)->count();
            $followingCount = DB::table('follows')->where('follower_id', $user->id)->count();

            $user->update([
                'followers_count' => $followersCount,
                'following_count' => $followingCount,
            ]);
        }
    }
}
