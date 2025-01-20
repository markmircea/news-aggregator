<?php

namespace App\Services\News;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Services\News\Interfaces\NewsServiceInterface;

abstract class BaseNewsService implements NewsServiceInterface
{
    protected string $apiKey;
    protected string $baseUrl;
    protected string $serviceName;

    public function __construct()
    {
        $this->apiKey = $this->getApiKey();
        $this->baseUrl = $this->getBaseUrl();
        $this->serviceName = $this->getServiceName();
    }

    public function fetchArticles(): array
    {
        try {
            $response = Http::get($this->baseUrl, $this->getRequestParams());

            if (!$response->successful()) {
                $this->logError('API error', [
                    'status' => $response->status(),
                    'body' => $response->json()
                ]);
                return [];
            }

            $articles = $this->extractArticlesFromResponse($response->json());
            return $this->formatArticles($articles);

        } catch (\Exception $e) {
            $this->logError('API exception', ['message' => $e->getMessage()]);
            return [];
        }
    }

    protected function logError(string $message, array $context = []): void
    {
        Log::error("{$this->serviceName}: {$message}", $context);
    }

    protected function formatArticles(array $articles): array
    {
        return array_map(function ($article) {
            return [
                'title' => $this->extractField($article, 'title'),
                'description' => $this->extractField($article, 'description'),
                'content' => $this->extractField($article, 'content'),
                'source_name' => $this->serviceName,
                'source_id' => $this->extractField($article, 'source_id', $this->serviceName),
                'author' => $this->extractField($article, 'author'),
                'url' => $this->extractField($article, 'url'),
                'image_url' => $this->extractField($article, 'image_url'),
                'category' => $this->extractField($article, 'category'),
                'published_at' => $this->extractField($article, 'published_at')
            ];
        }, $articles);
    }

    abstract protected function getApiKey(): string;
    abstract protected function getBaseUrl(): string;
    abstract protected function getServiceName(): string;
    abstract protected function getRequestParams(): array;
    abstract protected function extractArticlesFromResponse(array $response): array;
    abstract protected function extractField(array $article, string $field, $default = null): mixed;
}
