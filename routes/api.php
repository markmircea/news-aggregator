<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\ArticleController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('v1')->group(function () {
    Route::get('/articles', [ArticleController::class, 'index']);
    Route::get('/articles/search', [ArticleController::class, 'search']);
    Route::get('/categories', [ArticleController::class, 'categories']);
    Route::get('/sources', [ArticleController::class, 'sources']);
    Route::get('/authors', [ArticleController::class, 'authors']);
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
