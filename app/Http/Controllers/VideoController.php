<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Video;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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



    public function uploadChunk(Request $request)
{
    $chunk = $request->file('file');
    $filename = $request->input('filename');
    $title = $request->input('title');
    $description = $request->input('description');
    $chunkIndex = $request->input('chunk');
    $totalChunks = $request->input('totalChunks');
    $location = $request->input('location', 'testuploads');     // Sanitize location to prevent directory traversal
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


        $video = new Video();
        $video->user_id = auth()->id();
        $video->name = $filename;
        $video->title = $title;
        $video->description = $description;
        $video->file = $filename;
        $video->path = 'uploads/' . $location . '/'. $filename;
        $video->url = $videoUrl;
        


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
    public function discover($id)
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


