<?php

namespace App\Providers;

use App\Services\ArticleAggregationService;
use App\Services\CacheService;
use App\Services\News\NewsServiceCollection;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        try {
            $this->app->singleton(CacheService::class, function ($app) {
                return new CacheService();
            });

            $this->app->singleton(ArticleAggregationService::class, function ($app) {
                return new ArticleAggregationService(
                    $app->make(NewsServiceCollection::class),
                    $app->make(CacheService::class)
                );
            });

        } catch (\Exception $e) {
            Log::error('Service registration failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
