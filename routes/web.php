<?php

use App\Http\Controllers\ActsController;
use App\Http\Controllers\InvitationsController;
use App\Http\Controllers\MarkdownPagesController;
use App\Http\Controllers\WelcomeController;
use Illuminate\Support\Facades\Route;

Route::get('/acts/mine', [ActsController::class, 'mine'])
    ->middleware('auth')
    ->name('acts.mine');

Route::middleware('cache.headers:public;max_age=30;s_maxage=300;stale_while_revalidate=600;etag')->group(function () {
    Route::get('/', WelcomeController::class)->name('home');

    Route::controller(MarkdownPagesController::class)->group(function () {
        Route::get('/terms', 'show')->name('terms');
        Route::get('/policy', 'show')->name('policy');
        Route::get('/about', 'show')->name('about');
    });

    Route::resource('acts', ActsController::class)->only(['index', 'show']);
});

Route::get('/contact', function () {
    return view('contact_us.contact', ['title' => __('Contact Us')]);
})->middleware('throttle:5,1')
    ->name('contact-us');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
});

Route::resource('invitations', InvitationsController::class)
    ->only(['index', 'show'])
    ->middleware('auth');

require __DIR__.'/settings.php';
