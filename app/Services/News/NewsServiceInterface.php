<?php

namespace App\Services\News;

interface NewsServiceInterface
{
    public function fetchArticles(): array;
}
