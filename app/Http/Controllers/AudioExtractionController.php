<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use FFMpeg\FFMpeg;
use FFMpeg\Format\Audio\Mp3;
use App\Models\Video;


class AudioExtractionController extends Controller
{

public function extractAudio(Request $request)
{
    $videoId = $request->input('video_id');
    
    // Get video from database
    $video = Video::find($videoId);
    if (!$video) {
        return response()->json(['error' => 'Video not found'], 404);
    }

    $videoPath = storage_path('app/public/' . $video->path);
    $audioPath = str_replace('.mp4', '.mp3', $videoPath);

    // Check if video file exists
    if (!file_exists($videoPath)) {
        return response()->json(['error' => 'Video file not found'], 404);
    }

    try {
        // Initialize FFMpeg
        $ffmpeg = FFMpeg::create([
            'ffmpeg.binaries'  => env('FFMPEG_PATH', 'C:\\Users\\user\\Downloads\\ffmpeg-7.1.1-full_build\\bin\\ffmpeg.exe'),
            'ffprobe.binaries' => env('FFPROBE_PATH', 'C:\\Users\\user\\Downloads\\ffmpeg-7.1.1-full_build\\bin\\ffprobe.exe'),
        ]);

        // Open the video file
        $audio = $ffmpeg->open($videoPath);

        // Extract audio and save as MP3
        $audio->save(new Mp3(), $audioPath);

        // Update the video model with audio path if needed
        $video->audio_path = str_replace('.mp4', '.mp3', $video->path);
        $video->save();

        return response()->json([
            'message' => 'Audio extracted successfully',
            'audio_path' => $video->audio_path
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Audio extraction failed',
            'message' => $e->getMessage()
        ], 500);
    }
}}