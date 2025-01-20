# Laravel News Aggregator v10

## Project Setup and Configuration

### Database and Caching
- Using Redis for caching
- Using SQLite for database
- Run `php artisan migrate` if switching to a different database
- API middleware throttle enabled (60 requests/min)

### Key Files
- `news-aggregator/database/database.sqlite`
- `news-aggregator/docker-compose.yml`

## Running the Project

### Start Server
```bash
# Start Laravel development server
php artisan serve

# Start Redis server
docker compose up
Redis Commands
bashCopy# Access Redis bash
docker exec -it news-agg-redis redis-cli
Scheduler
bashCopy# Run scheduler (for development)
php artisan schedule:run

# Manually fetch latest articles
php artisan articles:fetch-latest

# Manually clear Redis cache
php artisan articles:clear-cache
API Endpoints
Articles
Get All Articles

Method: GET
URL: http://127.0.0.1:8000/api/v1/articles

Search Articles

Method: GET
URL: http://127.0.0.1:8000/api/v1/articles/search?q=technology

Filtered Articles

Method: GET
URL: http://127.0.0.1:8000/api/v1/articles?source=The Guardian&category=Technology

Other Endpoints

Categories: GET /api/v1/categories
Sources: GET /api/v1/sources
Authors: GET /api/v1/authors

Advanced Filtering Example
GET http://127.0.0.1:8000/api/v1/articles?source=The Guardian&category=Technology&from_date=2024-01-16&to_date=2025-01-17&per_page=10
Note: When using authors as a filter, use the key 'author'. Same applies to other filters (drop the 's'). NewsAPI does not have categories.
Project Structure
Environment Configuration

.env file contains:

API keys for Guardian, NewsAPI, NYTimes
SQLite database configuration
Redis configuration



Key Components

app/Console/Commands/

FetchLatestArticles.php: Artisan command to fetch articles
ClearArticlesCache.php: Clears Redis cache


app/Http/Controllers/API/ArticleController.php:

Handles API endpoints
Manages article retrieval with filters
Supports searching and filtering


app/Services/News/:

NewsServiceInterface.php: Defines news service interface
GuardianService.php: Fetches articles from The Guardian
NewsApiService.php: Fetches articles from NewsAPI
NYTimesService.php: Fetches articles from NYTimes


app/Services/:

ArticleAggregationService.php: Coordinates article fetching
CacheService.php: Manages Redis caching



Configuration Files

config/database.php: Added Redis caching configuration
config/services.php: Contains news API settings and endpoints


## Additional Notes
- Fetches latest articles for last 24 hours
- Accounts for timezones and late publications
- Ignores duplicate articles
- Invalidates cache automatically


Laravel v10 (Just because I like the kernal :) 

Its using redis for caching and sqlite for DB make sure to run php artisan migrate if switching to a different DB

first run the server
- php artisan serve

start the redis server
-docker compose up

start the scheduler with a cron job
- php artisan schedule:run

to get latest articles manually ->
- php artisan articles:fetch-latest

gets latest for last 24 hours (to account for timezones and late publications), ignores duplicates, invalidates cache

to manually clear redis cache
- php artisan articles:clear-cache



a. Get All Articles:
Method: GET
URL: http://127.0.0.1:8000/api/v1/articles

b. Search Articles:
Method: GET
URL: http://127.0.0.1:8000/api/v1/articles/search?q=technology

c. Filtered Articles:
Method: GET
URL: http://127.0.0.1:8000/api/v1/articles?source=The Guardian&category=Technology

d. Get Categories:
Method: GET
URL: http://127.0.0.1:8000/api/v1/categories

e. Get Sources:
Method: GET
URL: http://127.0.0.1:8000/api/v1/sources

f. Get Authors:
Method: GET
URL: http://127.0.0.1:8000/api/v1/authors

Example with filters (when using authors aas a filter, use the key 'author', sane for the rest, drop the 'S'):
Method: GET
URL: http://127.0.0.1:8000/api/v1/articles?source=The Guardian&category=Technology&from_date=2024-01-16&to_date=2025-01-17&per_page=10


MODIFIED FILES FROM ORIGINAL
news-aggregator/
│
├── .env
│   # Environment file containing API keys:
│   # GUARDIAN_API_KEY, NEWSAPI_KEY, NYTIMES_API_KEY
│   # Database configuration (SQLite)
│   # Added Redis configuration:
│   # REDIS_CLIENT=predis
│
├── app/
│   ├── Console/
│   │   ├── Commands/
│   │   │   ├── FetchLatestArticles.php
│   │   │   |    # Artisan command that runs the article fetching process
│   │   │   |    # Usage: php artisan articles:fetch-latest
│   │   │   └── ClearArticlesCache.php
|   |   |        # Clears the articles from redis with php artisan articles:clear-cache
|   |   |
│   │   └── Kernel.php
│   │       # Schedules the automatic fetching of articles every hour
│   │
│   ├── Http/
│   │   └── Controllers/
│   │       └── API/
│   │           └── ArticleController.php
│   │               # Handles API endpoints for:
│   │               # - Getting articles with filters
│   │               # - Searching articles
│   │               # - Getting categories, sources, authors
│   │
│   ├── Models/
│   │   └── Article.php
│   │       # Database model for articles
│   │       # Defines fillable fields and relationships
│   │
│   ├── Providers/
│   │   └── AppServiceProvider.php
│   │       # Registers news services in the Laravel container
│   │       # Configures service dependencies
│   │
│   └── Services/
│       ├── News/
│       │   ├── NewsServiceInterface.php
│       │   │   # Interface that defines how news services should work
│       │   │
│       │   ├── GuardianService.php
│       │   │   # Fetches articles from The Guardian API
│       │   │
│       │   ├── NewsApiService.php
│       │   │   # Fetches articles from NewsAPI
│       │   │
│       │   └── NYTimesService.php
│       │       # Fetches articles from NYTimes API (both top stories and search)
│       │
│       ├── ArticleAggregationService.php
│       |    # Coordinates fetching from all news sources
│       |    # Handles storing articles and avoiding duplicates
|       │
│       └── CacheService.php
│           # Caches the requests that are made for 24 hours so any subsequent requests go through redis
│           # Handles all Redis caching operations
│           # Manages cache keys and TTL
│           # Provides methods for cache invalidation
│           
│
├── config/
|   |
|   |
|   ├── database.php # added redis caching
│   | 
│   └── services.php
│       # Configuration file containing news API settings
│       # Stores API keys and endpoints configurations
│
├── database/
│   └── migrations/
│       └── [timestamp]_create_articles_table.php
│           # Creates the articles table with necessary fields:
│           # title, description, source, url, etc.
│
└── routes/
    └── api.php
        # Defines API routes for accessing articles:
        # GET /api/v1/articles
        # GET /api/v1/articles/search
        # GET /api/v1/categories
        # GET /api/v1/sources
        # GET /api/v1/authors


        news-aggregator\database\database.sqlite
        news-aggregator\docker-compose.yml
```
