<?php

namespace App\Http\Controllers;

use App\Services\ArticleService;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    protected ArticleService $articleService;

    public function __construct(ArticleService $articleService)
    {
        $this->articleService = $articleService;
    }

    public function index(Request $request)
    {
        $filters = $request->only(['source', 'category', 'date']);
        $articles = $this->articleService->getArticles($filters);

        return response()->json($articles);
    }

    public function search(Request $request)
    {
        $term = $request->get('q', '');
        $articles = $this->articleService->searchArticles($term);

        return response()->json($articles);
    }
}
