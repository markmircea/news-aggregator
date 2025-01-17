<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    protected $fillable = [
        'title',
        'description',
        'content',
        'source_name',
        'source_id',
        'author',
        'url',
        'image_url',
        'category',
        'published_at'
    ];

    protected $casts = [
        'published_at' => 'datetime'
    ];
}
