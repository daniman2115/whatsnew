<?php

// app/Jobs/ProcessVideo.php
namespace App\Jobs;

use App\Models\Video;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class processVideoWithFlask implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Video $video) {}

    public function handle()
    {
        try {
            $response = Http::timeout(120)
                ->withHeaders(['X-API-KEY' => config('services.flask.api_key')])
                ->post(config('services.flask.endpoint'), [
                    'video_url' => $this->video->url,
                    'video_id' => $this->video->id,
                ]);

            if ($response->failed()) {
                throw new \Exception("Flask API error: ".$response->body());
            }

            Log::info("Video {$this->video->id} processed successfully");

        } catch (\Exception $e) {
            Log::error("Video processing failed: ".$e->getMessage());
            $this->video->update(['processing_status' => 'failed']);
            throw $e; // Will trigger retries
        }
    }
}