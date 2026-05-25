<?php

use App\Http\Controllers\ActController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\MarkdownViewController;
use App\Http\Controllers\WelcomeController;
use Illuminate\Support\Facades\Route;

Route::middleware('cache.headers:public;max_age=30;s_maxage=300;stale_while_revalidate=600;etag')->group(function () {
    Route::get('/', WelcomeController::class)->name('home');

    Route::controller(MarkdownViewController::class)->group(function () {
        Route::get('/terms', 'show')->name('terms');
        Route::get('/policy', 'show')->name('policy');
        Route::get('/about', 'show')->name('about');


    });

    Route::resource('acts', ActController::class);
});


Route::get('/contact', function () {
    return view('contact_us.contact', ['title' => __('Contact Us')]);
})->middleware('throttle:5,1')
    ->name('contact-us');

// acts

Route::get('/acts/mine', [ActController::class, 'mine'])
    ->middleware([
        'auth',
    ])
    ->name('acts.mine');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
});

// invitations
Route::resource('invitations', InvitationController::class)->middleware([
    'auth',
]);

require __DIR__.'/settings.php';
