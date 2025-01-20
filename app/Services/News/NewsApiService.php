<?php

namespace App\Services\News;

class NewsApiService extends BaseNewsService
{
    protected function getApiKey(): string
    {
        return config('services.newsapi.api_key');
    }

    protected function getBaseUrl(): string
    {
        return config('services.newsapi.api_endpoint');
    }

    protected function getServiceName(): string
    {
        return 'NewsAPI';
    }

    protected function getRequestParams(): array
    {
        return [
            'apiKey' => $this->apiKey,
            'language' => 'en',
            'pageSize' => 100,
        ];
    }

    protected function extractArticlesFromResponse(array $response): array
    {
        return $response['articles'];
    }

    protected function extractField(array $article, string $field, $default = null): mixed
    {
        return match($field) {
            'title' => $article['title'],
            'description' => $article['description'],
            'content' => $article['content'],
            'source_id' => $article['source']['name'] ?? $default,
            'author' => $article['author'],
            'url' => $article['url'],
            'image_url' => $article['urlToImage'] ?? $default,
            'category' => $default, // NewsAPI doesn't provide category
            'published_at' => $article['publishedAt'],
            default => $default
        };
    }
}
