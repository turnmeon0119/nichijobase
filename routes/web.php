<?php

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

Route::get('/api/test', function () {
    return response()->json(['message' => 'OK']);
});
