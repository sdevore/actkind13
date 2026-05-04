<?php

use App\Http\Controllers\ActController;
use App\Http\Controllers\MarkdownViewController;
use App\Http\Controllers\WelcomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', WelcomeController::class)->name('home');
Route::get('/terms', [MarkdownViewController::class, 'show'])->name('terms');
Route::get('/policy', fn () => view('policy'))->name('policy');

Route::get('/contact-us', fn () => 'TBA')->name('contact-us');
Route::get('/about', fn () => 'TBA')->name('about');

// acts

Route::get('/acts/mine', [ActController::class, 'mine'])
    ->middleware([
        'auth',
    ])
    ->name('acts.mine');
Route::resource('acts', ActController::class);
Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';
