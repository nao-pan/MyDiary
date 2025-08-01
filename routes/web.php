<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DiaryController;
use App\Http\Controllers\StatusController;

// 認証前のルーティング
Route::get('/', function () {
    return redirect()->route('diary.index');// 初期アクセス時は一覧ページへ
});
Route::get('/welcome', function () {
    return view('welcome');
})->name('welcome');
// その他の静的ページ
Route::view('/about', 'about')->name('about');

// 認証後のルーティング
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::resource('diary', DiaryController::class);
    Route::resource('status', StatusController::class);
});
require __DIR__.'/auth.php';
