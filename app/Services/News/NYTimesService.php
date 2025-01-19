<?php

namespace App\Services\News;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class NYTimesService implements NewsServiceInterface
{
    private string $apiKey;
    private string $topStoriesBaseUrl = 'https://api.nytimes.com/svc/topstories/v2';
    private string $searchBaseUrl = 'https://api.nytimes.com/svc/search/v2/articlesearch.json';
    private array $sections = ['home', 'technology', 'science', 'world', 'business'];

    public function __construct()
    {
        $this->apiKey = config('services.nytimes.api_key');
    }

    public function fetchArticles(): array
    {
        $allArticles = [];

        // Fetch top stories
        $topStories = $this->fetchTopStories();
        $allArticles = array_merge($allArticles, $topStories);

        // Fetch recent articles from search API (last 24 hours)
        $searchResults = $this->fetchRecentArticles();
        $allArticles = array_merge($allArticles, $searchResults);

        // Remove duplicates based on URL
        $uniqueArticles = collect($allArticles)->unique('url')->values()->all();

        return $uniqueArticles;
    }

    private function fetchTopStories(): array
    {
        $articles = [];

        foreach ($this->sections as $section) {
            try {
                $response = Http::get("{$this->topStoriesBaseUrl}/{$section}.json", [
                    'api-key' => $this->apiKey
                ]);

                if (!$response->successful()) {
                    Log::error('NYTimes Top Stories API error', [
                        'section' => $section,
                        'status' => $response->status(),
                        'body' => $response->json()
                    ]);
                    continue;
                }

                $articles = array_merge(
                    $articles,
                    $this->formatArticles($response->json()['results'], $section)
                );

            } catch (\Exception $e) {
                Log::error('NYTimes Top Stories API exception', [
                    'section' => $section,
                    'message' => $e->getMessage()
                ]);
            }
        }

        return $articles;
    }

    private function fetchRecentArticles(): array
    {
        try {
            $yesterday = Carbon::now()->subDay()->format('Ymd');

            $response = Http::get($this->searchBaseUrl, [
                'api-key' => $this->apiKey,
                'begin_date' => $yesterday,
                'sort' => 'newest',
                'fl' => 'web_url,headline,abstract,pub_date,news_desk,multimedia,byline,section_name'
            ]);

            if (!$response->successful()) {
                Log::error('NYTimes Article Search API error', [
                    'status' => $response->status(),
                    'body' => $response->json()
                ]);
                return [];
            }

            return $this->formatSearchResults($response->json()['response']['docs']);

        } catch (\Exception $e) {
            Log::error('NYTimes Article Search API exception', [
                'message' => $e->getMessage()
            ]);
            return [];
        }
    }

    private function formatArticles(array $articles, string $section): array
    {
        return array_map(function ($article) use ($section) {
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
                'title' => $article['title'],
                'description' => $article['abstract'],
                'content' => null,
                'source_name' => 'The New York Times',
                'author' => $article['byline'],
                'url' => $article['url'],
                'image_url' => $imageUrl,
                'category' => $article['section'] ?? $section,
                'published_at' => $article['published_date']
            ];
        }, $articles);
    }

    private function formatSearchResults(array $articles): array
    {
        return array_map(function ($article) {
            $imageUrl = null;
            if (!empty($article['multimedia'])) {
                foreach ($article['multimedia'] as $media) {
                    if ($media['type'] === 'image') {
                        $imageUrl = 'https://www.nytimes.com/' . $media['url'];
                        break;
                    }
                }
            }

            return [
                'title' => $article['headline']['main'],
                'description' => $article['abstract'],
                'content' => null,
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
