<?php

use App\Http\Controllers\AdminArticlePageController;
use App\Http\Controllers\AdminArticleCommentPageController;
use App\Http\Controllers\AdminNewsPageController;
use App\Http\Controllers\AdminOgiriPageController;
use App\Http\Controllers\BoardPageController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/admin/articles/login');

Route::get('/admin/articles/login', [AdminArticlePageController::class, 'login'])->name('admin.articles.login');
Route::post('/admin/articles/login', [AdminArticlePageController::class, 'authenticate'])->name('admin.articles.authenticate');

Route::middleware('admin.web.token')->group(function (): void {
    Route::get('/admin', [AdminArticlePageController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/admin/articles', [AdminArticlePageController::class, 'index'])->name('admin.articles.index');
    Route::get('/admin/articles/create', [AdminArticlePageController::class, 'create'])->name('admin.articles.create');
    Route::get('/admin/articles/trash', [AdminArticlePageController::class, 'trash'])->name('admin.articles.trash');
    Route::post('/admin/articles', [AdminArticlePageController::class, 'store'])->name('admin.articles.store');
    Route::get('/admin/articles/{article}/edit', [AdminArticlePageController::class, 'edit'])->name('admin.articles.edit');
    Route::put('/admin/articles/{article}', [AdminArticlePageController::class, 'update'])->name('admin.articles.update');
    Route::delete('/admin/articles/{article}', [AdminArticlePageController::class, 'destroy'])->name('admin.articles.destroy');
    Route::patch('/admin/articles/{article}/restore', [AdminArticlePageController::class, 'restore'])->name('admin.articles.restore');
    Route::delete('/admin/articles/{article}/force', [AdminArticlePageController::class, 'forceDestroy'])->name('admin.articles.force-destroy');
    Route::get('/admin/article-comments', [AdminArticleCommentPageController::class, 'index'])->name('admin.article-comments.index');
    Route::delete('/admin/article-comments/{comment}', [AdminArticleCommentPageController::class, 'destroy'])->name('admin.article-comments.destroy');
    Route::get('/admin/news', [AdminNewsPageController::class, 'index'])->name('admin.news.index');
    Route::get('/admin/news/create', [AdminNewsPageController::class, 'create'])->name('admin.news.create');
    Route::post('/admin/news', [AdminNewsPageController::class, 'store'])->name('admin.news.store');
    Route::get('/admin/news/{newsItem}/edit', [AdminNewsPageController::class, 'edit'])->name('admin.news.edit');
    Route::put('/admin/news/{newsItem}', [AdminNewsPageController::class, 'update'])->name('admin.news.update');
    Route::delete('/admin/news/{newsItem}', [AdminNewsPageController::class, 'destroy'])->name('admin.news.destroy');
    Route::get('/admin/ogiri', [AdminOgiriPageController::class, 'index'])->name('admin.ogiri.index');
    Route::get('/admin/ogiri/create', [AdminOgiriPageController::class, 'create'])->name('admin.ogiri.create');
    Route::post('/admin/ogiri', [AdminOgiriPageController::class, 'store'])->name('admin.ogiri.store');
    Route::delete('/admin/ogiri/{prompt}', [AdminOgiriPageController::class, 'destroy'])->name('admin.ogiri.destroy');
    Route::post('/admin/articles/logout', [AdminArticlePageController::class, 'logout'])->name('admin.articles.logout');
    Route::get('/admin/board', [BoardPageController::class, 'index'])->name('admin.board.index');
    Route::get('/admin/timeline', [BoardPageController::class, 'timeline'])->name('admin.board.timeline');
    Route::get('/admin/board/{thread}', [BoardPageController::class, 'show'])->name('admin.board.show');
    Route::post('/admin/board', [BoardPageController::class, 'storeThread'])->name('admin.board.store');
    Route::post('/admin/board/{thread}/posts', [BoardPageController::class, 'storePost'])->name('admin.board.posts.store');
    Route::delete('/admin/board/{thread}/posts/{post}', [BoardPageController::class, 'destroyPost'])->name('admin.board.posts.destroy');
    Route::delete('/admin/board/{thread}', [BoardPageController::class, 'destroy'])->name('admin.board.destroy');
});

Route::get('/api/test', function () {
    return response()->json(['message' => 'OK']);
});
