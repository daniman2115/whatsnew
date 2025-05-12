<?php

namespace App\Http\Controllers;

use App\Models\Like;
use App\Models\Video;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    public function like(Video $video){
        $userId = 1;
        if($video->likes()->where('user_id', $userId)->exists()) {
        return response()->json(['message' => 'Already liked'], 409);
    }

    $video->likes()->create(['user_id' => $userId]);

    $video->increment('like_count');

    return response()->json([
        'message' => 'Video liked successfully',
        'likes_count' => $video->like_count
    ]);
}
}
