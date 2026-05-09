<?php

use App\Http\Controllers\ActController;
use App\Http\Controllers\MarkdownViewController;
use App\Http\Controllers\WelcomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', WelcomeController::class)->name('home');

Route::controller(MarkdownViewController::class)->group(function () {
    Route::get('/terms', 'show')->name('terms');
    Route::get('/policy', 'show')->name('policy');
    Route::get('/about', 'show')->name('about');
});

Route::get('/contact', function () {
    return view('contact_us.contact', ['title' => __('Contact Us')]);
})->name('contact-us');

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
