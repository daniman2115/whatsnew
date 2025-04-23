<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Video;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function comment(Video $video, Request $request)
    {
        $userId = 1;
    
        $request->validate([
            'comment' => 'required|string|max:500'
        ]);
    
        // Create comment
        $comment = $video->comments()->create([
            'user_id' => $userId,
            'comment' => $request->comment
        ]);
    
        // Increment comment count
        $video->increment('comment_count');
    
        return response()->json([
            'message' => 'Comment added successfully',
            'comment_count' => $video->comment_count,
            'comment' => $comment
        ]);
    }
}
