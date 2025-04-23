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
        $videos = Video::where('user_id',$id)->get();
        $totalLikes = $videos->sum('like_count');


        return response()->json(
            ["user" => $user, 
                   "likes" => $totalLikes,
                   'videos' => $videos,
    ]);
    }
}
