<?php

namespace App\Providers;

use App\Services\ArticleAggregationService;
use App\Services\News\GuardianService;
use App\Services\News\NewsApiService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(ArticleAggregationService::class, function ($app) {
            return new ArticleAggregationService([
                new GuardianService(),
                new NewsApiService(),
            ]);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
