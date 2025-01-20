<?php

namespace App\Services;

use App\Models\Article;
use App\Services\News\NewsServiceCollection;
use Illuminate\Support\Facades\Log;

class ArticleAggregationService
{
    private NewsServiceCollection $newsServices;
    private CacheService $cacheService;

    public function __construct(
        NewsServiceCollection $newsServices,
        CacheService $cacheService
    ) {
        $this->newsServices = $newsServices;
        $this->cacheService = $cacheService;
    }

    public function aggregateLatestArticles(): array
    {
        $stats = [
            'total_fetched' => 0,
            'total_new' => 0,
            'errors' => 0,
            'database_total' => 0
        ];

        foreach ($this->newsServices->getServices() as $service) {
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

        $stats['database_total'] = Article::count();

        return $stats;
    }

    private function storeArticle(array $articleData, array &$stats): void
    {
        try {
            $created = Article::updateOrCreate(
                ['url' => $articleData['url']],
                $articleData
            );

            if ($created->wasRecentlyCreated) {
                $stats['total_new']++;
                $this->invalidateRelevantCaches($created);
            }
        } catch (\Exception $e) {
            $stats['errors']++;
            Log::error('Error storing article', [
                'error' => $e->getMessage(),
                'article' => $articleData
            ]);
        }
    }

    private function invalidateRelevantCaches(Article $article): void
    {
        $this->cacheService->invalidateCache('filtered:*');
        $this->cacheService->invalidateCache('search:*');
        $this->cacheService->invalidateCache('categories');
        $this->cacheService->invalidateCache('sources');
        $this->cacheService->invalidateCache('authors');
    }
}
