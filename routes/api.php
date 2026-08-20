<?php

use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\BoardPostController;
use App\Http\Controllers\Api\BoardThreadController;
use App\Http\Controllers\Api\NewsItemController;
use App\Http\Controllers\Api\OgiriAnswerController;
use App\Http\Controllers\Api\OgiriPromptController;
use Illuminate\Support\Facades\Route;

Route::get('/articles', [ArticleController::class, 'index']);
Route::get('/articles/{slug}', [ArticleController::class, 'show']);
Route::get('/news', [NewsItemController::class, 'index']);
Route::get('/news/{slug}', [NewsItemController::class, 'show']);
Route::get('/articles/{slug}/thread', [BoardThreadController::class, 'showByArticle']);
Route::get('/threads', [BoardThreadController::class, 'index']);
Route::get('/threads/{thread}', [BoardThreadController::class, 'show']);
Route::post('/threads', [BoardThreadController::class, 'store'])->middleware('throttle:20,1');
Route::post('/threads/{thread}/posts', [BoardPostController::class, 'store'])->middleware('throttle:40,1');
Route::post('/threads/{thread}/report', [BoardThreadController::class, 'report'])->middleware('throttle:10,1');
Route::post('/threads/{thread}/reactions', [BoardThreadController::class, 'react'])->middleware('throttle:10,1');
Route::post('/threads/{thread}/posts/{post}/report', [BoardPostController::class, 'report'])->middleware('throttle:20,1');
Route::post('/threads/{thread}/posts/{post}/reactions', [BoardPostController::class, 'react'])->middleware('throttle:20,1');
Route::get('/ogiri/prompts', [OgiriPromptController::class, 'index']);
Route::get('/ogiri/prompts/{prompt}', [OgiriPromptController::class, 'show']);
Route::post('/ogiri/prompts/{prompt}/answers', [OgiriAnswerController::class, 'store'])->middleware('throttle:30,1');
Route::post('/ogiri/prompts/{prompt}/answers/{answer}/reactions', [OgiriAnswerController::class, 'react'])->middleware('throttle:30,1');

Route::middleware('admin.api.token')->group(function (): void {
    Route::post('/articles', [ArticleController::class, 'store']);
    Route::post('/ogiri/prompts', [OgiriPromptController::class, 'store']);
    Route::put('/articles/{slug}', [ArticleController::class, 'update']);
    Route::delete('/articles/{slug}', [ArticleController::class, 'destroy']);
    Route::delete('/articles/id/{article}', [ArticleController::class, 'destroyById']);
    Route::delete('/threads/{thread}', [BoardThreadController::class, 'destroy']);
    Route::delete('/threads/{thread}/posts/{post}', [BoardPostController::class, 'destroy']);
    Route::patch('/threads/{thread}/hide', [BoardThreadController::class, 'hide']);
    Route::patch('/threads/{thread}/unhide', [BoardThreadController::class, 'unhide']);
    Route::patch('/threads/{thread}/posts/{post}/hide', [BoardPostController::class, 'hide']);
    Route::patch('/threads/{thread}/posts/{post}/unhide', [BoardPostController::class, 'unhide']);
});
