<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Str;

class VideoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $videos = Video::latest()->get();
        
        return response()->json([
            'videos' => $videos,
            'storage_url' => url('/storage/videos/') // Base URL for video access
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */    
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'file' => 'required|file|mimes:mp4,mov,avi|max:102400',
            'name' => 'required|string|max:255', 
        ]);

        try {
            $file = $request->file('file');
            $customName = $request->name;

            // Create safe filename with extension
            $filename = Str::slug($customName).'.'.$file->getClientOriginalExtension();
            
            // Store in storage/app/public/videos
            $path = $file->storeAs('videos', $filename);
            
            $video = new Video();
            $video->user_id = 1; // Use authenticated user if available
            $video->name = $customName;
            $video->title = $request->title;
            $video->description = $request->description;
            $video->file = $filename;
            $video->path = $path;
            $video->url = Storage::url($path);
            $video->save();

            return response()->json([
                'message' => 'Video uploaded successfully',
                'video' => $video,
                'url' => $video->url
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'File upload failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Video $video)
    {
        return response()->json([
            'video' => $video,
            'playable_url' => $video->url // Direct URL to the video file
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Video $video)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Video $video)
    {
        try {
            // Delete the physical file
            Storage::delete($video->path);
            
            // Delete the database record
            $video->delete();

            return response()->json([
                'message' => 'Video deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Video deletion failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    



    // For You feed - random or algorithmic content
    public function forYou()
    {
        $posts = Video::with('user')->inRandomOrder()->take(20)->get();
        return response()->json($posts);
    }



        public function following($id)
        {
            $user = User::find($id);

                // Handle case where user is not found
            if (!$user) {
                return response()->json(['error' => 'User not found'], 404);
            }
    
            // Get user IDs the current user is following
            $followingIds = $user->following()->pluck('following_id');
    
            // Fetch videos by those users
            $videos = Video::whereIn('user_id', $followingIds)
                            ->orderBy('created_at', 'desc')
                            ->with('user') // eager load user info
                            ->get(10);
    
            return response()->json($videos);
        
    }
    



    // Discover feed - maybe trending or hashtags, simplified here as latest posts
    public function discover()
    {
        $posts = Video::with('user')->inRandomOrder()->take(20)->get();
        return response()->json($posts);
    }




    public function search(Request $request)
    {
        $query = $request->input('query');
    
        if (!$query) {
            return response()->json([
                'message' => 'Query is required.'
            ], 400);
        }
    
        $videos = Video::where('title', 'like', '%' . $query . '%')
                       ->orWhere('description', 'like', '%' . $query . '%')
                       ->get();
    
        return response()->json([
            'results' => $videos
        ]);
    }
    
}


