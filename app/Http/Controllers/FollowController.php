<?php

namespace App\Http\Controllers;

use App\Models\Follow;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FollowController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function showFollowers($id)
    {
        $user = User::find($id);
        $followers = $user->followers()->get();

        return response()->json($followers);
    }


    public function showFollowing($id)
    {
        $user = User::find($id);
        $following = $user->following()->get();

        return response()->json($following);
    }



    




    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Follow $follow)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Follow $follow)
    {
        //
    }



    public function follow(User $user)
    {
        $follower = auth()->user();

        return DB::transaction(function () use ($follower, $user) {
            // Check if already following using proper column names
            $alreadyFollowing = DB::table('follows')
                ->where('follower_id', $follower->id)
                ->where('following_id', $user->id)
                ->exists();

            if ($alreadyFollowing) {
                return response()->json(['message' => 'Already following'], 409);
            }

            // Create with proper timestamps
            DB::table('follows')->insert([
                'follower_id' => $follower->id,
                'following_id' => $user->id,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Update counters
            $follower->update(['following_count' => $follower->following_count + 1]);
            $user->increment('followers_count');

            return response()->json([
                'message' => 'Followed successfully',
                'followers_count' => $user->fresh()->followers_count,
                'following_count' => $follower->fresh()->following_count
            ]);
        });
    }

    public function unfollow(User $user)
    {
        $follower = auth()->user();

        return DB::transaction(function () use ($follower, $user) {
            $isFollowing = DB::table('follows')
                ->where('follower_id', $follower->id)
                ->where('following_id', $user->id)
                ->exists();

            if (!$isFollowing) {
                return response()->json(['message' => 'Not following'], 404);
            }

            DB::table('follows')
                ->where('follower_id', $follower->id)
                ->where('following_id', $user->id)
                ->delete();

            $follower->update(['following_count' => $follower->following_count - 1]);
            $user->decrement('followers_count');

            return response()->json([
                'message' => 'Unfollowed successfully',
                'followers_count' => $user->fresh()->followers_count,
                'following_count' => $follower->fresh()->following_count
            ]);
        });
    }

}
