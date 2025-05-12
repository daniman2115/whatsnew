<?php
namespace App\Services;

use App\Models\Video;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HuggingFaceService
{
    public function detectFakeNews(string $text): bool 
{
    $response = Http::withOptions([
        'verify' => false,
        'timeout' => 30,
    ])->withHeaders([
        'Authorization' => 'Bearer ' . env('HUGGINGFACE_API_KEY'),
    ])->post('https://api-inference.huggingface.co/models/jy46604790/Fake-News-Bert-Detect', [
        'inputs' => $text
    ]);

    // Retrieve and decode the response
    $result = $response->json();

    Log::debug('Hugging Face API Response', ['response' => $result]);

    // Check if the structure is correct (direct array of results)
    if (!is_array($result) || !isset($result[0][0]['label']) || !isset($result[0][0]['score'])) {
        Log::error('Unexpected Hugging Face API response structure', ['response' => $result]);
        return false;
    }

    // Access the label and score from the array
    $label = $result[0][0]['label'];
    $score = $result[0][0]['score'];

    Log::debug('Parsed Hugging Face Response', [
        'input_text' => $text,
        'label' => $label,
        'score' => $score
    ]);

    // 'LABEL_0' corresponds to fake news and 'LABEL_1' to real news
    // If label is 'LABEL_0' (fake news) and score is greater than 0.5, mark as fake
    return strtoupper($label) === 'LABEL_0' && $score > 0.5;
}



/**
     * Get recommended videos based on text similarity
     */
    public function recommendVideos(string $userQuery): array
    {
        // Step 1: Get embeddings for the user's search query
        $response = Http::withOptions([
            'verify' => false,
            'timeout' => 30,
        ])->withHeaders([
            'Authorization' => 'Bearer ' . env('HUGGINGFACE_API_KEY'),
        ])->post('https://api-inference.huggingface.co/models/sentence-transformers/all-MiniLM-L6-v2', [
            'inputs' => $userQuery
        ]);

        $queryEmbedding = $response->json();

        if (!is_array($queryEmbedding)) {
            Log::error('Failed to generate embeddings for recommendation');
            return [];
        }

        // Step 2: Compare with stored video embeddings (from database)
        $videos = Video::all();
        $recommendations = [];

        foreach ($videos as $video) {
            // (Ideally, pre-compute & store embeddings in DB)
            $similarityScore = $this->cosineSimilarity($queryEmbedding, $video->embedding);

            if ($similarityScore > 0.7) { // Threshold for relevance
                $recommendations[] = [
                    'video_id' => $video->id,
                    'title' => $video->title,
                    'similarity_score' => $similarityScore
                ];
            }
        }

        // Sort by highest similarity
        usort($recommendations, fn($a, $b) => $b['similarity_score'] <=> $a['similarity_score']);

        return $recommendations;
    }

    /**
     * Helper: Compute cosine similarity between two vectors
     */
    private function cosineSimilarity(array $vecA, array $vecB): float
    {
        $dotProduct = array_sum(array_map(fn($a, $b) => $a * $b, $vecA, $vecB));
        $normA = sqrt(array_sum(array_map(fn($a) => $a * $a, $vecA)));
        $normB = sqrt(array_sum(array_map(fn($b) => $b * $b, $vecB)));

        return $dotProduct / ($normA * $normB);
    }
}