<?php

use App\Http\Controllers\AdminArticlePageController;
use App\Http\Controllers\BoardPageController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/board', [BoardPageController::class, 'index'])->name('board.index');
Route::get('/timeline', [BoardPageController::class, 'timeline'])->name('board.timeline');
Route::get('/board/{thread}', [BoardPageController::class, 'show'])->name('board.show');
Route::post('/board', [BoardPageController::class, 'storeThread'])->name('board.store');
Route::post('/board/{thread}/posts', [BoardPageController::class, 'storePost'])->name('board.posts.store');

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
    Route::post('/admin/articles/logout', [AdminArticlePageController::class, 'logout'])->name('admin.articles.logout');
    Route::delete('/admin/threads/{thread}', [BoardPageController::class, 'destroy'])->name('admin.threads.destroy');
});

Route::get('/api/test', function () {
    return response()->json(['message' => 'OK']);
});
