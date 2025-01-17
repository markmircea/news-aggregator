<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('content')->nullable();
            $table->string('source_name');  // Guardian/NewsAPI source name
            $table->string('source_id')->nullable();  // For NewsAPI source ID
            $table->string('author')->nullable();
            $table->string('url')->unique();  // Using as unique identifier
            $table->string('image_url')->nullable();
            $table->string('category')->nullable();
            $table->timestamp('published_at');
            $table->timestamps();

            $table->index('source_name');
            $table->index('category');
            $table->index('published_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
