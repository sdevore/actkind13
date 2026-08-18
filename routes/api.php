<?php

use App\Actions\Api\User\LogoutUserAction;
use App\Actions\Api\User\ShowUserAction;
use App\Http\Controllers\ActController;
use App\Http\Controllers\AppreciateController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\SanctumTokenController;
use Illuminate\Support\Facades\Route;

Route::post('/sanctum/token', SanctumTokenController::class)->name('sanctum.token');

Route::name('guest.')->group(function () {
    Route::controller(ActController::class)
        ->name('acts.')
        ->group(function () {
            Route::get('/acts', 'api_index')->name('index');
            Route::get('/acts/{act}', 'api_public_show')->name('show');
        });
});

Route::middleware('auth:sanctum')
    ->group(function () {
        Route::prefix('user')
            ->name('user.')
            ->group(function () {
                Route::get('/', ShowUserAction::class)->name('show');
                Route::post('/logout', LogoutUserAction::class)->name('logout');
            });

        Route::prefix('private')
            ->group(function () {
                Route::prefix('acts')
                    ->controller(ActController::class)
                    ->name('acts.')
                    ->group(function () {
                        Route::get('/', 'api_index')
                            ->name('index');
                        Route::put('/', 'api_store')->name('store');
                        Route::get('/mine', 'api_mine')->name('mine');
                        Route::get('/{act}', 'api_show')->name('show');
                        Route::post('/{act}', 'api_update')->name('update');
                        Route::delete('/{act}', 'api_destroy')->name('destroy');
                    });

                Route::prefix('acts/{act}')
                    ->name('acts.')
                    ->group(function () {
                        Route::put('/appreciations', [AppreciateController::class, 'store'])
                            ->name('appreciations.store');
                        Route::put('/comments', [CommentController::class, 'store'])
                            ->name('comments.store');
                    });

                Route::prefix('comments')
                    ->controller(CommentController::class)
                    ->name('comments.')
                    ->group(function () {
                        Route::post('/{comment}', 'update')->name('update');
                        Route::delete('/{comment}', 'destroy')->name('destroy');
                    });

                Route::prefix('appreciations')
                    ->controller(AppreciateController::class)
                    ->name('appreciations.')
                    ->group(function () {
                        Route::delete('/{appreciation}', 'destroy')->name('destroy');
                    });
            });
    });
