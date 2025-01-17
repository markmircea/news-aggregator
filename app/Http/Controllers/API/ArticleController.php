<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;

class ArticleController extends Controller
{
    public function index(Request $request)
    {
        $query = Article::query();
        $this->applyFilters($query, $request);

        $articles = $query->latest('published_at')
                         ->paginate($request->get('per_page', 15));

        return response()->json([
            'status' => 'success',
            'data' => $articles
        ]);
    }

    public function search(Request $request)
    {
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

        return response()->json([
            'status' => 'success',
            'data' => $articles
        ]);
    }

    public function categories()
    {
        $categories = Article::distinct()
                           ->whereNotNull('category')
                           ->pluck('category');

        return response()->json([
            'status' => 'success',
            'data' => $categories
        ]);
    }

    public function sources()
    {
        $sources = Article::distinct()
                         ->pluck('source_name');

        return response()->json([
            'status' => 'success',
            'data' => $sources
        ]);
    }

    public function authors()
    {
        $authors = Article::distinct()
                         ->whereNotNull('author')
                         ->pluck('author');

        return response()->json([
            'status' => 'success',
            'data' => $authors
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
