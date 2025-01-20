<?php

namespace App\Services\News\Interfaces;

interface NewsServiceInterface
{
    public function fetchArticles(): array;
}
