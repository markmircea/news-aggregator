<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Services\CacheService;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;

class ArticleController extends Controller
{
    private CacheService $cacheService;

    public function __construct(CacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    public function index(Request $request)
    {
        // Build cache key from request parameters
        $filters = array_filter($request->only([
            'source',
            'category',
            'author',
            'from_date',
            'to_date',
            'per_page',
            'page'
        ]));

        $cacheKey = 'filtered:' . $this->cacheService->buildFilterKey($filters);

        // Try to get from cache first
        if ($cached = $this->cacheService->getCachedArticles($cacheKey)) {
            return response()->json([
                'status' => 'success',
                'data' => $cached,
                'from_cache' => true
            ]);
        }

        // If not in cache, get from database
        $query = Article::query();
        $this->applyFilters($query, $request);

        $articles = $query->latest('published_at')
                         ->paginate($request->get('per_page', 15));

        // Cache the results
        $this->cacheService->cacheArticles($cacheKey, $articles);

        return response()->json([
            'status' => 'success',
            'data' => $articles,
            'from_cache' => false
        ]);
    }

    public function search(Request $request)
    {
        $filters = array_filter($request->only([
            'q',
            'source',
            'category',
            'author',
            'from_date',
            'to_date',
            'per_page',
            'page'
        ]));

        $cacheKey = 'search:' . $this->cacheService->buildFilterKey($filters);

        if ($cached = $this->cacheService->getCachedArticles($cacheKey)) {
            return response()->json([
                'status' => 'success',
                'data' => $cached,
                'from_cache' => true
            ]);
        }

        $query = Article::query();

        if ($search = $request->get('q')) {
            $query->where(function (Builder $query) use ($search) {
                $query->where('title', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%")
                      ->orWhere('source_name', 'like', "%{$search}%")
                      ->orWhere('author', 'like', "%{$search}%")
                      ->orWhere('category', 'like', "%{$search}%")
                      ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $this->applyFilters($query, $request);

        $articles = $query->latest('published_at')
                         ->paginate($request->get('per_page', 15));

        $this->cacheService->cacheArticles($cacheKey, $articles);

        return response()->json([
            'status' => 'success',
            'data' => $articles,
            'from_cache' => false
        ]);
    }

    public function categories()
    {
        $cacheKey = 'categories';

        if ($cached = $this->cacheService->getCachedArticles($cacheKey)) {
            return response()->json([
                'status' => 'success',
                'data' => $cached,
                'from_cache' => true
            ]);
        }

        $categories = Article::distinct()
                           ->whereNotNull('category')
                           ->pluck('category');

        $this->cacheService->cacheArticles($cacheKey, $categories);

        return response()->json([
            'status' => 'success',
            'data' => $categories,
            'from_cache' => false
        ]);
    }

    public function sources()
    {
        $cacheKey = 'sources';

        if ($cached = $this->cacheService->getCachedArticles($cacheKey)) {
            return response()->json([
                'status' => 'success',
                'data' => $cached,
                'from_cache' => true
            ]);
        }

        $sources = Article::distinct()
                         ->pluck('source_name');

        $this->cacheService->cacheArticles($cacheKey, $sources);

        return response()->json([
            'status' => 'success',
            'data' => $sources,
            'from_cache' => false
        ]);
    }

    public function authors()
    {
        $cacheKey = 'authors';

        if ($cached = $this->cacheService->getCachedArticles($cacheKey)) {
            return response()->json([
                'status' => 'success',
                'data' => $cached,
                'from_cache' => true
            ]);
        }

        $authors = Article::distinct()
                         ->whereNotNull('author')
                         ->pluck('author');

        $this->cacheService->cacheArticles($cacheKey, $authors);

        return response()->json([
            'status' => 'success',
            'data' => $authors,
            'from_cache' => false
        ]);
    }

    private function applyFilters(Builder $query, Request $request): void
    {
        if ($source = $request->get('source')) {
            $query->where('source_name', $source);
        }

        if ($category = $request->get('category')) {
            $query->where('category', $category);
        }

        if ($author = $request->get('author')) {
            $query->where('author', $author);
        }

        if ($fromDate = $request->get('from_date')) {
            $query->whereDate('published_at', '>=', $fromDate);
        }

        if ($toDate = $request->get('to_date')) {
            $query->whereDate('published_at', '<=', $toDate);
        }
    }
}
