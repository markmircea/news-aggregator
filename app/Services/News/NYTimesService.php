<?php

namespace App\Services\News;

class NYTimesService extends BaseNewsService
{
    protected function getApiKey(): string
    {
        return config('services.nytimes.api_key');
    }

    protected function getBaseUrl(): string
    {
        return 'https://api.nytimes.com/svc/search/v2/articlesearch.json';
    }

    protected function getServiceName(): string
    {
        return 'The New York Times';
    }

    protected function getRequestParams(): array
    {
        return [
            'api-key' => $this->apiKey,
            'begin_date' => now()->subDay()->format('Ymd'),
            'sort' => 'newest',
            'page-size' => 100,
            'fl' => 'web_url,headline,abstract,snippet,pub_date,news_desk,multimedia,byline,section_name'
        ];
    }

    protected function extractArticlesFromResponse(array $response): array
    {
        return $response['response']['docs'];
    }

    protected function extractField(array $article, string $field, $default = null): mixed
    {
        return match($field) {
            'title' => $article['headline']['main'],
            'description' => $article['abstract'],
            'content' => $article['snippet'] ?? $default,
            'author' => $article['byline']['original'] ?? $default,
            'url' => $article['web_url'],
            'image_url' => $this->extractImageUrl($article),
            'category' => $article['section_name'] ?? $article['news_desk'],
            'published_at' => $article['pub_date'],
            default => $default
        };
    }

    private function extractImageUrl(array $article): ?string
    {
        if (empty($article['multimedia'])) {
            return null;
        }

        foreach ($article['multimedia'] as $media) {
            if ($media['type'] === 'image') {
                return $media['url'];
            }
        }

        return null;
    }
}
