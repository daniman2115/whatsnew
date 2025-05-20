<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Video;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
public function showProfile($id)
{
    $user = User::find($id);
    $videos = Video::where('user_id', $id)->get();
    $totalLikes = $videos->sum('like_count');
    
    // Add is_following status if authenticated
    if (auth()->check()) {
        $user->is_following = auth()->user()->isFollowing($user);
    } else {
        $user->is_following = false;
    }

    return response()->json([
        "user" => $user, 
        "likes" => $totalLikes,
        'videos' => $videos,
    ]);
}



    // app/Http/Controllers/ProfileController.php

// public function show(User $user)
// {
//     $authUser = auth()->user();
//     $isFollowing = $authUser ? $authUser->isFollowing($user) : false;

//     return response()->json([
//         'user' => [
//             ...$user->toArray(),
//             'is_following' => $isFollowing,
//             'followers_count' => $user->followers()->count(),
//             'following_count' => $user->following()->count()
//         ],
//         'videos' => $user->videos()->withCount('likes')->get(),
//         'likes' => $user->videos()->sum('likes_count')
//     ]);
// }


public function editProfile(Request $request)
{
    $user = auth()->user();
    $user->update([
        'name' => $request->input('name'),
        'email' => $request->input('email'),
        'username' => $request->input('username'),
        'bio' => $request->input('bio'),
        // 'profile_picture' => $request->input('profile_picture'),
    ]);

    return response()->json([
        'message' => 'Profile updated successfully',
        'user' => $user
    ]);
    }
}
