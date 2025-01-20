<?php

namespace App\Services\News;

class GuardianService extends BaseNewsService
{
    protected function getApiKey(): string
    {
        return config('services.guardian.api_key');
    }

    protected function getBaseUrl(): string
    {
        return 'https://content.guardianapis.com/search';
    }

    protected function getServiceName(): string
    {
        return 'The Guardian';
    }

    protected function getRequestParams(): array
    {
        return [
            'api-key' => $this->apiKey,
            'show-fields' => 'all',
            'page-size' => 200,
            'order-by' => 'newest',
            'from-date' => now()->subDay()->format('Y-m-d'),
            'to-date' => now()->format('Y-m-d')
        ];
    }

    protected function extractArticlesFromResponse(array $response): array
    {
        return $response['response']['results'];
    }

    protected function extractField(array $article, string $field, $default = null): mixed
    {
        return match($field) {
            'title' => $article['webTitle'],
            'description' => $article['fields']['trailText'] ?? $default,
            'content' => $article['fields']['bodyText'] ?? $default,
            'author' => $article['fields']['byline'] ?? $default,
            'url' => $article['webUrl'],
            'image_url' => $article['fields']['thumbnail'] ?? $default,
            'category' => $article['sectionName'],
            'published_at' => $article['webPublicationDate'],
            default => $default
        };
    }
}
