<?php

namespace App\Services\News;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GuardianService implements NewsServiceInterface
{
    private string $apiKey;
    private string $baseUrl = 'https://content.guardianapis.com/search';

    public function __construct()
    {
        $this->apiKey = config('services.guardian.api_key');
    }

    public function fetchArticles(): array
    {
        try {
            $response = Http::get($this->baseUrl, [
                'api-key' => $this->apiKey,
                'show-fields' => 'all',
                'page-size' => 50,
                'order-by' => 'newest',
                'from-date' => now()->subDay()->format('Y-m-d'),  //last 24 hours
                'to-date' => now()->format('Y-m-d')
            ]);

            if (!$response->successful()) {
                Log::error('Guardian API error', [
                    'status' => $response->status(),
                    'body' => $response->json()
                ]);
                return [];
            }

            return $this->formatArticles($response->json()['response']['results']);
        } catch (\Exception $e) {
            Log::error('Guardian API exception', ['message' => $e->getMessage()]);
            return [];
        }
    }

    private function formatArticles(array $articles): array
    {
        return array_map(function ($article) {
            return [
                'title' => $article['webTitle'],
                'description' => $article['fields']['trailText'] ?? null,
                'content' => $article['fields']['bodyText'] ?? null,
                'source_name' => 'The Guardian',
                'author' => $article['fields']['byline'] ?? null,
                'url' => $article['webUrl'],
                'image_url' => $article['fields']['thumbnail'] ?? null,
                'category' => $article['sectionName'],
                'published_at' => $article['webPublicationDate']
            ];
        }, $articles);
    }
}
