<?php

namespace App\Services\News;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NewsApiService implements NewsServiceInterface
{
    private string $apiKey;
    private string $baseUrl = 'https://newsapi.org/v2/top-headlines';

    public function __construct()
    {
        $this->apiKey = config('services.newsapi.api_key');
    }

    public function fetchArticles(): array
    {
        try {
            $response = Http::get($this->baseUrl, [
                'apiKey' => $this->apiKey,
                'language' => 'en',
                'pageSize' => 50,
                'from' => now()->subDay()->format('Y-m-d\TH:i:s\Z'),  // Last 24 hours
                'to' => now()->format('Y-m-d\TH:i:s\Z')
            ]);

            if (!$response->successful()) {
                Log::error('NewsAPI error', [
                    'status' => $response->status(),
                    'body' => $response->json()
                ]);
                return [];
            }

            return $this->formatArticles($response->json()['articles']);
        } catch (\Exception $e) {
            Log::error('NewsAPI exception', ['message' => $e->getMessage()]);
            return [];
        }
    }

    private function formatArticles(array $articles): array
    {
        return array_map(function ($article) {
            return [
                'title' => $article['title'],
                'description' => $article['description'],
                'content' => $article['content'],
                'source_name' => $article['source']['name'] ?? 'NewsAPI',
                'source_id' => $article['source']['id'] ?? null,
                'author' => $article['author'],
                'url' => $article['url'],
                'image_url' => $article['urlToImage'] ?? null,
                'category' => null, // NewsAPI top-headlines doesn't provide category
                'published_at' => $article['publishedAt']
            ];
        }, $articles);
    }
}
