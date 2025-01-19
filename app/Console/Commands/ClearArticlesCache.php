<?php

namespace App\Console\Commands;

use App\Services\CacheService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ClearArticlesCache extends Command
{
    protected $signature = 'articles:clear-cache';
    protected $description = 'Clear all cached article data';

    private CacheService $cacheService;

    public function __construct(CacheService $cacheService)
    {
        parent::__construct();
        $this->cacheService = $cacheService;
    }

    public function handle()
    {
        Cache::tags(['articles'])->flush();
        $this->info('Articles cache cleared successfully');
    }
}
