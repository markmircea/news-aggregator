<?php

namespace App\Services;

use App\Models\Article;
use App\Services\News\NewsServiceInterface;
use Illuminate\Support\Facades\Log;

class ArticleAggregationService
{
    private array $newsServices;

    public function __construct(array $newsServices)
    {
        $this->newsServices = $newsServices;
    }

    public function aggregateLatestArticles(): array
    {
        $stats = [
            'total_fetched' => 0,
            'total_new' => 0,
            'errors' => 0
        ];

        foreach ($this->newsServices as $service) {
            try {
                $articles = $service->fetchArticles();
                $stats['total_fetched'] += count($articles);

                foreach ($articles as $articleData) {
                    $this->storeArticle($articleData, $stats);
                }
            } catch (\Exception $e) {
                $stats['errors']++;
                Log::error('Error fetching articles', [
                    'service' => get_class($service),
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $stats;
    }

    private function storeArticle(array $articleData, array &$stats): void
    {
        try {
            $created = Article::updateOrCreate(
                ['url' => $articleData['url']],  // URL as unique identifier
                $articleData
            );

            if ($created->wasRecentlyCreated) {
                $stats['total_new']++;
            }
        } catch (\Exception $e) {
            $stats['errors']++;
            Log::error('Error storing article', [
                'error' => $e->getMessage(),
                'article' => $articleData
            ]);
        }
    }
}
