<?php

namespace App\Http\Controllers;

use App\Models\Like;
use App\Models\Video;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    // LikeController.php
public function like(Video $video)
{
    $userId = auth()->id();
    
    if($video->likes()->where('user_id', $userId)->exists()) {
        return response()->json(['message' => 'Already liked'], 409);
    }

    $video->likes()->create(['user_id' => $userId]);
    $video->increment('like_count');

    return response()->json([
        'message' => 'Video liked successfully',
        'likes_count' => $video->like_count,
        'is_liked' => true
    ]);
}

public function unlike(Video $video)
{
    $userId = auth()->id();
    $deleted = $video->likes()
                   ->where('user_id', $userId)
                   ->delete();

    if ($deleted) {
        $video->decrement('like_count');
        return response()->json([
            'message' => 'Video unliked successfully',
            'likes_count' => $video->like_count,
            'is_liked' => false

        ]);
    }

    return response()->json(['message' => 'Like not found'], 404);
}
    public function showLikes(Video $video)
    {
        $likes = $video->likes()->with('user')->get();

        return response()->json([
            'likes' => $likes
        ]);
    }

}
