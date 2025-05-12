<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Video;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Video>
 */
class VideoFactory extends Factory
{

    protected $model = Video::class;


    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {

        $user = User::all()->random();

        return [
            'user_id' => $user->id, // Random user for the video
            'title' => $this->faker->sentence(5),
            'name' => $this->faker->word,
            'description' => $this->faker->sentence(),
            'transcribed_text' => $this->faker->sentence(),
            'url' => $this->faker->url(),
            'path' => $this->faker->word . '.mp4', // Assuming it's a video file
            'thumbnail_path' => $this->faker->word . '.mp4', // Assuming it's a video file
            'file' => $this->faker->word . '.mp4', // Path or name of the video file
            'thumbnail_url' => $this->faker->imageUrl(200, 200, 'video'),
            'duration_seconds' => $this->faker->numberBetween(30, 600), // Video length between 30s and 10 minutes
            'is_premium' => $this->faker->boolean(),
            'credibility_score' => $this->faker->randomFloat(2, 0, 1), // 0-1 score
            'last_credibility_check' => now(),
            'status' => $this->faker->randomElement(['pending', 'approved', 'rejected']),
            'reviewed_by' => User::inRandomOrder()->first()->id, // Random user who reviewed
            'allow_likes' => true, // By default, likes are allowed
            'allow_comments' => true, // By default, comments are allowed
            'allow_shares' => true, // By default, shares are allowed
            'like_count' => $this->faker->numberBetween(0, 1000),
            'comment_count' => $this->faker->numberBetween(0, 500),
            'share_count' => $this->faker->numberBetween(0, 500),
            'view_count' => $this->faker->numberBetween(0, 10000),
        ];
    }
}
