<?php

namespace App\Providers;

use App\Services\News\GuardianService;
use App\Services\News\NewsApiService;
use App\Services\News\NewsServiceCollection;
use App\Services\News\NewsServiceInterface;
use App\Services\News\NYTimesService;
use Illuminate\Support\ServiceProvider;

class NewsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(NewsServiceInterface::class . 'guardian', function ($app) {
            return new GuardianService();
        });

        $this->app->bind(NewsServiceInterface::class . 'newsapi', function ($app) {
            return new NewsApiService();
        });

        $this->app->bind(NewsServiceInterface::class . 'nytimes', function ($app) {
            return new NYTimesService();
        });

        $this->app->singleton(NewsServiceCollection::class, function ($app) {
            return new NewsServiceCollection([
                $app->make(NewsServiceInterface::class . 'guardian'),
                $app->make(NewsServiceInterface::class . 'newsapi'),
                $app->make(NewsServiceInterface::class . 'nytimes')
            ]);
        });
    }
}
