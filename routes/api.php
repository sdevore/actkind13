<?php

use App\Http\Controllers\Api\ActAppreciationsController;
use App\Http\Controllers\Api\ActCommentsController;
use App\Http\Controllers\Api\ActsController;
use App\Http\Controllers\Api\AppreciationsController;
use App\Http\Controllers\Api\CommentsController;
use App\Http\Controllers\Api\Guest\ActsController as GuestActsController;
use App\Http\Controllers\Api\LogoutController;
use App\Http\Controllers\Api\MyActsController;
use App\Http\Controllers\Api\SanctumTokenController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::name('api.')->group(function () {
    Route::post('/sanctum/token', SanctumTokenController::class)->name('sanctum.token');

    Route::name('guest.')->group(function () {
        Route::controller(GuestActsController::class)
            ->name('acts.')
            ->group(function () {
                Route::get('/acts', 'index')->name('index');
                Route::get('/acts/{act}', 'show')->name('show');
            });
    });

    Route::middleware('auth:sanctum')
        ->group(function () {
            Route::prefix('user')
                ->name('user.')
                ->group(function () {
                    Route::get('/', [UserController::class, 'show'])->name('show');
                    Route::post('/logout', LogoutController::class)->name('logout');
                });

            Route::prefix('private')
                ->name('private.')
                ->group(function () {
                    Route::prefix('acts')
                        ->name('acts.')
                        ->group(function () {
                            Route::get('/mine', [MyActsController::class, 'index'])->name('mine');

                            Route::controller(ActsController::class)->group(function () {
                                Route::get('/', 'index')->name('index');
                                Route::put('/', 'store')->name('store');
                                Route::get('/{act}', 'show')->name('show');
                                Route::post('/{act}', 'update')->name('update');
                                Route::delete('/{act}', 'destroy')->name('destroy');
                            });
                        });

                    Route::prefix('acts/{act}')
                        ->name('acts.')
                        ->group(function () {
                            Route::put('/appreciations', [ActAppreciationsController::class, 'store'])
                                ->name('appreciations.store');
                            Route::put('/comments', [ActCommentsController::class, 'store'])
                                ->name('comments.store');
                        });

                    Route::prefix('comments')
                        ->controller(CommentsController::class)
                        ->name('comments.')
                        ->group(function () {
                            Route::post('/{comment}', 'update')->name('update');
                            Route::delete('/{comment}', 'destroy')->name('destroy');
                        });

                    Route::delete('/appreciations/{appreciation}', [AppreciationsController::class, 'destroy'])
                        ->name('appreciations.destroy');
                });
        });
});
