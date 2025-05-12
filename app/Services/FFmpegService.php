<?php

namespace App\Services;

use FFMpeg\FFMpeg;
use FFMpeg\Format\Video\X264;
use FFMpeg\Format\Audio\Mp3;
use FFMpeg\Format\Audio\Aac;
use FFMpeg\Coordinate\TimeCode;
use Illuminate\Support\Facades\Log;

class FFmpegService
{
    protected $ffmpeg;

    public function __construct()
    {
        $ffmpegPath = env('FFMPEG_PATH', 'C:\\Users\\user\\Downloads\\ffmpeg-7.1.1-full_build\\bin\\ffmpeg.exe');
        $ffprobePath = env('FFPROBE_PATH', 'C:\\Users\\user\\Downloads\\ffmpeg-7.1.1-full_build\\bin\\ffprobe.exe');

        if (!file_exists($ffmpegPath)) {
            throw new \Exception("FFmpeg binary not found at: {$ffmpegPath}");
        }

        if (!file_exists($ffprobePath)) {
            throw new \Exception("FFprobe binary not found at: {$ffprobePath}");
        }

        $this->ffmpeg = FFMpeg::create([
            'ffmpeg.binaries'  => $ffmpegPath,
            'ffprobe.binaries' => $ffprobePath,
            'timeout'          => 3600,
            'ffmpeg.threads'   => 12,
        ]);
    }

    // public function extractThumbnail($videoPath, $thumbnailPath, $second = 5)
    // {
    //     if (!file_exists($videoPath)) {
    //         throw new \Exception("Video file not found at: {$videoPath}");
    //     }

    //     try {
    //         // Verify the thumbnail directory exists
    //         if (!is_dir(dirname($thumbnailPath))) {
    //             mkdir(dirname($thumbnailPath), 0755, true);
    //         }

    //         // Using direct command for better error handling
    //         $ffmpegPath = env('FFMPEG_PATH');
    //         $command = sprintf(
    //             '"%s" -i "%s" -ss %d -vframes 1 -q:v 2 "%s" 2>&1',
    //             $ffmpegPath,
    //             $videoPath,
    //             $second,
    //             $thumbnailPath
    //         );

    //         exec($command, $output, $returnCode);

    //         if ($returnCode !== 0 || !file_exists($thumbnailPath)) {
    //             throw new \Exception("FFmpeg command failed: " . implode("\n", $output));
    //         }

    //         return true;
    //     } catch (\Exception $e) {
    //         Log::error("FFmpeg Thumbnail Generation Failed", [
    //             'video_path' => $videoPath,
    //             'thumbnail_path' => $thumbnailPath,
    //             'error' => $e->getMessage(),
    //             'command_output' => $output ?? []
    //         ]);
    //         throw $e;
    //     }
    // }

    /**
     * Extract audio from video file
     *
     * @param string $videoPath Path to the input video file
     * @param string $audioOutputPath Path where the audio file should be saved
     * @param string $format Audio format (mp3 or aac)
     * @return bool
     * @throws \Exception
     */
    public function extractAudio($videoPath, $audioOutputPath, $format = 'mp3')
    {
        if (!file_exists($videoPath)) {
            throw new \Exception("Video file not found at: {$videoPath}");
        }

        try {
            // Verify the output directory exists
            if (!is_dir(dirname($audioOutputPath))) {
                mkdir(dirname($audioOutputPath), 0755, true);
            }

            $video = $this->ffmpeg->open($videoPath);

            switch (strtolower($format)) {
                case 'mp3':
                    $audioFormat = new Mp3();
                    break;
                case 'aac':
                    $audioFormat = new Aac();
                    break;
                default:
                    throw new \Exception("Unsupported audio format: {$format}");
            }

            $video->save($audioFormat, $audioOutputPath);

            if (!file_exists($audioOutputPath)) {
                throw new \Exception("Audio extraction failed - output file not created");
            }

            return true;
        } catch (\Exception $e) {
            Log::error("FFmpeg Audio Extraction Failed", [
                'video_path' => $videoPath,
                'audio_output_path' => $audioOutputPath,
                'format' => $format,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get audio duration in seconds
     *
     * @param string $audioPath Path to the audio file
     * @return float Duration in seconds
     * @throws \Exception
     */
    public function getAudioDuration($audioPath)
    {
        if (!file_exists($audioPath)) {
            throw new \Exception("Audio file not found at: {$audioPath}");
        }

        try {
            $ffprobePath = env('FFPROBE_PATH');
            $command = sprintf(
                '"%s" -i "%s" -show_entries format=duration -v quiet -of csv="p=0"',
                $ffprobePath,
                $audioPath
            );

            $duration = exec($command);

            return (float)$duration;
        } catch (\Exception $e) {
            Log::error("FFprobe Duration Check Failed", [
                'audio_path' => $audioPath,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}