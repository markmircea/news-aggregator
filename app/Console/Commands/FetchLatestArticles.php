<?php

namespace App\Console\Commands;

use App\Services\ArticleAggregationService;
use Illuminate\Console\Command;

class FetchLatestArticles extends Command
{
    protected $signature = 'articles:fetch-latest';
    protected $description = 'Fetch latest articles from the last 24 hours';

    private ArticleAggregationService $aggregationService;

    public function __construct(ArticleAggregationService $aggregationService)
    {
        parent::__construct();
        $this->aggregationService = $aggregationService;
    }

    public function handle()
    {
        $this->info('Starting to fetch latest articles...');

        $stats = $this->aggregationService->aggregateLatestArticles();

        $this->info("Fetching completed:");
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Fetched', $stats['total_fetched']],
                ['New Articles', $stats['total_new']],
                ['Errors', $stats['errors']]
            ]
        );
    }
}
