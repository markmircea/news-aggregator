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

        $this->info('Fetching completed:');
        $this->table(
            ['Metric', 'Value'],
            [
                ['Articles From APIs', $stats['total_fetched']],
                ['New Articles Added', $stats['total_new']],
                ['Total Articles in Database', $stats['database_total']],
                ['Errors', $stats['errors']]
            ]
        );
    }
}
