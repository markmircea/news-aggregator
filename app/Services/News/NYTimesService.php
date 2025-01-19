<?php

namespace App\Services\News;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class NYTimesService implements NewsServiceInterface
{
    private string $apiKey;
    private string $searchBaseUrl = 'https://api.nytimes.com/svc/search/v2/articlesearch.json';

    public function __construct()
    {
        $this->apiKey = config('services.nytimes.api_key');
    }

    public function fetchArticles(): array
    {
        try {
            $yesterday = Carbon::now()->subDay()->format('Ymd');

            $response = Http::get($this->searchBaseUrl, [
                'api-key' => $this->apiKey,
                'begin_date' => $yesterday,
                'sort' => 'newest',
                'fl' => 'web_url,headline,abstract,snippet,pub_date,news_desk,multimedia,byline,section_name'
            ]);

            if (!$response->successful()) {
                Log::error('NYTimes Article Search API error', [
                    'status' => $response->status(),
                    'body' => $response->json()
                ]);
                return [];
            }

            return $this->formatArticles($response->json()['response']['docs']);

        } catch (\Exception $e) {
            Log::error('NYTimes Article Search API exception', [
                'message' => $e->getMessage()
            ]);
            return [];
        }
    }

    private function formatArticles(array $articles): array
    {
        return array_map(function ($article) {
            $imageUrl = null;
            if (!empty($article['multimedia'])) {
                foreach ($article['multimedia'] as $media) {
                    if ($media['type'] === 'image') {
                        $imageUrl = $media['url'];
                        break;
                    }
                }
            }

            return [
                'title' => $article['headline']['main'],
                'description' => $article['abstract'],
                'content' => $article['snippet'] ?? null,
                'source_name' => 'The New York Times',
                'author' => $article['byline']['original'] ?? null,
                'url' => $article['web_url'],
                'image_url' => $imageUrl,
                'category' => $article['section_name'] ?? $article['news_desk'],
                'published_at' => $article['pub_date']
            ];
        }, $articles);
    }
}
