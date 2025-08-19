<?php

use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\DiaryController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StatusController;
use Illuminate\Support\Facades\Route;

// 認証前のルーティング
Route::get('/', function () {
    return redirect()->route('diary.index'); // 初期アクセス時は一覧ページへ
})->middleware('auth');

Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('admin.dashboard');
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

// テスト用 breeze のルーティング
Route::get('/dashboard', function () {
    return redirect()->route('diary.index');
})->name('dashboard');

require __DIR__.'/auth.php';


Route::get('/auth/google/redirect', [SocialAuthController::class, 'redirect'])
     ->name('auth.google.redirect');
Route::get('/auth/google/callback', [SocialAuthController::class, 'callback'])
     ->name('auth.google.callback');