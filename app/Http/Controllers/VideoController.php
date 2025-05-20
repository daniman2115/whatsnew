<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Video;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Str;
use App\Services\HuggingFaceService;
use App\Services\FFmpegService;


class VideoController extends Controller
{
    protected $ffmpegService;

    public function __construct(FFmpegService $ffmpegService)
    {
        $this->ffmpegService = $ffmpegService;
    }


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
    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'title' => 'required|string|max:255',
    //         'description' => 'nullable|string',
    //         'file' => 'required|file|mimes:mp4,mov,avi|max:102400',
    //         'name' => 'required|string|max:255', 
    //     ]);

    //     try {
    //         $file = $request->file('file');
    //         $customName = $request->name;

    //         // Create safe filename with extension
    //         $filename = Str::slug($customName).'.'.$file->getClientOriginalExtension();
            
    //         // Store in storage/app/public/videos
    //         $path = $file->storeAs('videos', $filename);
            
    //         $video = new Video();
    //         $video->user_id = 1; // Use authenticated user if available
    //         $video->name = $customName;
    //         $video->title = $request->title;
    //         $video->description = $request->description;
    //         $video->file = $filename;
    //         $video->path = $path;
    //         $video->url = Storage::url($path);
    //         $video->save();

    //         return response()->json([
    //             'message' => 'Video uploaded successfully',
    //             'video' => $video,
    //             'url' => $video->url
    //         ]);

    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'message' => 'File upload failed',
    //             'error' => $e->getMessage()
    //         ], 500);
    //     }
    // }







    public function uploadChunk(Request $request)
{
    $chunk = $request->file('file');
    $filename = $request->input('filename');
    $title = $request->input('title');
    $description = $request->input('description');
    $chunkIndex = $request->input('chunk');
    $totalChunks = $request->input('totalChunks');
    $location = $request->input('location');
    // Sanitize location to prevent directory traversal
    $location = str_replace(['..', '/'], '', $location);
    $tempDir = storage_path('app/temp_chunks/' . $filename);
    $finalDir = storage_path('app/public/uploads/' . $location);
    $finalPath = $finalDir . '/' . $filename;

    // Create temp directory if it doesn't exist
    if (!file_exists($tempDir) && !mkdir($tempDir, 0777, true) && !is_dir($tempDir)) {
        return response()->json(['message' => 'Failed to create temp directory'], 500);
    }
    $chunk->move($tempDir, $chunkIndex);

    // Check if all chunks are uploaded
    $files = scandir($tempDir);
    if (count($files) - 2 === (int) $totalChunks) { // Ignore '.' and '..'
        
        // Ensure the final directory exists
        if (!file_exists($finalDir) && !mkdir($finalDir, 0777, true) && !is_dir($finalDir)) {
            return response()->json(['message' => 'Failed to create uploads directory'], 500);
        }
               
        // Open the final file path for writing
        if (!is_dir($finalDir)) {
            return response()->json(['message' => 'Uploads directory does not exist'], 500);
        }

        // Open the final file path for writing
        $output = fopen($finalPath, 'wb');
        if (!$output) {
            return response()->json(['message' => 'Failed to open final file path'], 500);
        }

        // Combine chunks into one file
        for ($i = 0; $i < $totalChunks; $i++) {
            $chunkPath = $tempDir . '/' . $i;
            $chunkFile = fopen($chunkPath, 'rb');
            if ($chunkFile) {
                stream_copy_to_stream($chunkFile, $output);
                fclose($chunkFile);
            } else {
                return response()->json(['message' => 'Failed to open chunk file'], 500);
            }
        }

        fclose($output);

        // Get the full URL to the video
        $videoUrl = url('storage/uploads/'.$location.'/'.$filename);

        // Call Flask API to get credibility score
        // $flaskApiUrl = env('FLASK_API_URL', 'http://localhost:5000/api/process-video');
        // $apiKey = env('FLASK_API_KEY');

        // try {
        //     $response = Http::withHeaders([
        //         'X-API-KEY' => $apiKey,
        //         'Content-Type' => 'application/json',
        //     ])->post($flaskApiUrl, [
        //         'video_url' => $videoUrl,
        //     ]);

        //     $credibilityScore = null;
        //     if ($response->successful()) {
        //         $responseData = $response->json();
        //         $credibilityScore = $responseData['credibility_score'] ?? null;
        //     }
        // } catch (\Exception $e) {
        //     // Log the error but continue with video creation
        //     \Log::error("Failed to get credibility score: " . $e->getMessage());
        //     $credibilityScore = null;
        // }

        $video = new Video();
        // $video->user_id = 1; // Use authenticated user if available
        $video->user_id = auth()->id();
        $video->name = $filename;
        $video->title = $title;
        $video->description = $description;
        $video->file = $filename;
        $video->path = 'uploads/' . $location . '/'. $filename;
        $video->url = $videoUrl;
        
        // // Add credibility score if available
        // if ($credibilityScore !== null) {
        //     $video->credibility_score = $credibilityScore;
        // }

        $video->save();

        // Clean up temporary chunks and directory
        array_map('unlink', glob("$tempDir/*"));
        rmdir($tempDir);
        
        return response()->json([
            'message' => 'File uploaded successfully!',
            'file_path' => 'uploads/' . $location . '/'. $filename,
            'url' => $video->url,
        ], 200);
    }

    return response()->json(['message' => 'Chunk uploaded successfully.']);
}
//     private function processVideoWithFlask(Video $video)
// {
//     try {
//         $client = new Client([
//             'timeout' => 120,
//             'connect_timeout' => 30,
//             'verify' => false // Only for development!
//         ]);

//         $response = $client->post(config('services.flask.api_url').'/api/process-video', [
//             'json' => [
//                 'video_url' => $video->url,
//                 'video_id' => $video->id
//             ],
//             'headers' => [
//                 'X-API-KEY' => config('services.flask.api_key'),
//                 'Accept' => 'application/json'
//             ]
//         ]);

//         $body = json_decode($response->getBody(), true);
        
//         if (!isset($body['status']) || $body['status'] !== 'success') {
//             throw new \Exception("Flask API returned unsuccessful status");
//         }

//         return $body;

//     } catch (\GuzzleHttp\Exception\RequestException $e) {
//         Log::error("Flask connection failed: " . $e->getMessage());
//         throw new \Exception("Video processing service unavailable");
//     }
// }

    /**
     * Display the specified resource.
     */

// public function show()
// {
//     // Eager load the user relationship with specific fields
//     $videos = Video::with(['user:id,name,username,profile_picture'])
//      ->orderBy('created_at', 'DESC')
//      ->get();
    
//     $videos->transform(function($video) {
//         // Ensure URL is properly formatted
//         $video->url;
        
//         // Add fallback if user relationship doesn't exist
//         if (!$video->user) {
//             $video->user = (object)[
//                 'id' => 0,
//                 'name' => 'Deleted User',
//                 'username' => 'deleted',
//                 'profile_picture' => asset('images/default-avatar.png')
//             ];
//         }
        
//         return $video;
//     });
    
//     return $videos;
// }


// public function show()
// {
//     $videos = Video::with([
//         'user:id,name,username,profile_picture',
//         'comments.user:id,name,username,profile_picture',
//         'likes:user_id,video_id,' // Include likes
//     ])
//     ->orderBy('created_at', 'DESC')
//     ->get();
    
//     $videos->transform(function($video) {
//         // Ensure URL is properly formatted
//         $video->url;
        
//         // Add fallback if user relationship doesn't exist
//         if (!$video->user) {
//             $video->user = (object)[
//                 'id' => 0,
//                 'name' => 'Deleted User',
//                 'username' => 'deleted',
//                 'profile_picture' => asset('images/default-avatar.png')
//             ];
//         }
        
//         // Format comments if they exist
//         if ($video->comments) {
//             $video->comments->transform(function($comment) {
//                 if (!$comment->user) {
//                     $comment->user = (object)[
//                         'id' => 0,
//                         'name' => 'Deleted User',
//                         'username' => 'deleted',
//                         'profile_picture' => asset('images/default-avatar.png')
//                     ];
//                 }
//                 return $comment;
//             });
//         }
        
//         return $video;
//     });
    
//     return $videos;
// }




public function show()
{
    $userId = auth()->id();
    
    $videos = Video::with([
        'user:id,name,username,profile_picture',
        'comments.user:id,name,username,profile_picture',
        'likes:user_id,video_id'
    ])
    ->orderBy('created_at', 'DESC')
    ->get()
    ->map(function($video) use ($userId) {
        $video->is_liked = $video->likes->contains('user_id', $userId);
        return $video;
    });
    
    return $videos;
}





public function showProfile($userId)
{
    $currentUserId = auth()->id();
    
    // Get user with their videos
    $user = User::with(['videos' => function($query) {
        $query->select('id', 'user_id', 'title', 'url', 'created_at', 'like_count');
    }])
    ->withCount(['followers', 'following'])
    ->findOrFail($userId);
    
    // Calculate total likes
    $totalLikes = $user->videos->sum('like_count');
    
    // Check if current user is following this user
    $isFollowing = false;
    if ($currentUserId) {
        $isFollowing = DB::table('follows')
            ->where('follower_id', $currentUserId)
            ->where('following_id', $userId)
            ->exists();
    }
    
    // Format video URLs
    $user->videos->transform(function($video) {
        $video->url;
        return $video;
    });
    
    return response()->json([
        'user' => $user->only(['id', 'name', 'username', 'profile_picture', 'bio', 'followers_count', 'following_count']),
        'videos' => $user->videos,
        'likes' => $totalLikes,
        'is_following' => $isFollowing // Include this in the response
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
                            ->get();
    
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



    public function checkVideo($id, HuggingFaceService $hf)
{
    $video = Video::find($id);
    $isFake = $hf->detectFakeNews($video->description);
 
    if ($isFake){
    return response()->json([
      "message" => "This video is fake",
    ]);
}

    else{
        return response()->json([
            "message" => "This video is real",
        ]);
    }
}






// public function generateThumbnail(Video $video)
// {
//     try {
//         // Get the video path from the database record
//         $videoPath = storage_path('app/public/' . $video->path);
        
//         // Set thumbnail paths
//         $thumbnailDir = storage_path('app/public/thumbnails');
//         $thumbnailFilename = $video->id . '_thumbnail.jpg';
//         $thumbnailPath = $thumbnailDir . '/' . $thumbnailFilename;
//         $relativeThumbnailPath = 'thumbnails/' . $thumbnailFilename;

//         // Create directory if it doesn't exist
//         if (!file_exists($thumbnailDir)) {
//             mkdir($thumbnailDir, 0755, true);
//         }

//         // Generate thumbnail using FFmpegService
//         $this->ffmpegService->extractThumbnail($videoPath, $thumbnailPath, 10);

//         // Update the video record with thumbnail path
//         $video->update(['thumbnail_path' => $relativeThumbnailPath]);

//         return response()->json([
//             'success' => true,
//             'message' => 'Thumbnail generated successfully',
//             'thumbnail_path' => $relativeThumbnailPath
//         ]);

//     } catch (\Exception $e) {
//         return response()->json([
//             'success' => false,
//             'error' => 'Thumbnail generation failed',
//             'message' => $e->getMessage(),
//             'details' => [
//                 'video_id' => $video->id,
//                 'video_path' => $video->path ?? 'Not found',
//                 'attempted_thumbnail_path' => $relativeThumbnailPath ?? 'Not generated'
//             ]
//         ], 500);
//     }
// }




public function extractAudio($videoPath, $audioOutputPath, $format = 'mp3')
{
    if (!file_exists($videoPath)) {
        throw new \Exception("Video file not found at: {$videoPath}");
    }

    try {
        if (!is_dir(dirname($audioOutputPath))) {
            mkdir(dirname($audioOutputPath), 0755, true);
        }

        $ffmpegPath = env('FFMPEG_PATH');
        $command = sprintf(
            '"%s" -i "%s" -vn -acodec libmp3lame -ab 192k "%s"',
            $ffmpegPath,
            $videoPath,
            $audioOutputPath
        );

        exec($command, $output, $returnCode);

        if ($returnCode !== 0 || !file_exists($audioOutputPath)) {
            throw new \Exception("FFmpeg command failed: " . implode("\n", $output));
        }

        return true;
    } catch (\Exception $e) {
        Log::error("FFmpeg Audio Extraction Failed", [
            'video_path' => $videoPath,
            'audio_output_path' => $audioOutputPath,
            'format' => $format,
            'error' => $e->getMessage(),
            'command_output' => $output ?? []
        ]);
        throw $e;
    }
}
}


