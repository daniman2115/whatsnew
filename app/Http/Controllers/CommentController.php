<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Video;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function comment(Video $video, Request $request)
    {
        $userId = auth()->user()->id;
    
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


    public function showComments(Video $video)
    {
        $comments = $video->comments()->with('user')->get();
    
        return response()->json([
            'comments' => $comments
        ]);
    }
    public function deleteComment(Comment $comment)
    {
        $comment->delete();
    
        return response()->json([
            'message' => 'Comment deleted successfully'
        ]);
    }

}

