<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

// Les routes articles
Route::get('article', [\App\Http\Controllers\ArticleController::class, 'index'])->name('article.index');
Route::middleware('auth')->group(function() {
    Route::post('article', [\App\Http\Controllers\ArticleController::class, 'store'])->name('article.store');
    Route::get('article/{article}', [\App\Http\Controllers\ArticleController::class, 'show'])->name('article.show');
    Route::put('article/{article}', [\App\Http\Controllers\ArticleController::class, 'update'])->name('article.update');
    Route::delete('article/{article}', [\App\Http\Controllers\ArticleController::class, 'destroy'])->name('article.destroy');

    // "Invokable controller" CurrentUserArticlesController n'a qu'une seule méthode __invoke qui sera appelée par la route
    Route::get('my_articles', \App\Http\Controllers\CurrentUserArticlesController::class);
});
