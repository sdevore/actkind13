<?php

use App\Http\Controllers\ActController;
use App\Http\Controllers\SanctumTokenController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//    return $request->user();
// })->middleware('auth:sanctum');

Route::name('user.')->prefix('user')
    ->middleware('auth:sanctum')
    ->group(function () {
        Route::get('/', function (Request $request) {
            return $request->user();
        })->name('show');

        Route::post('/logout', function (Request $request) {
            $user = $request->user();
            $token = $request->user('sanctum')->currentAccessToken();
            $token->delete();

            return response()->json(['message' => 'Logged out successfully.']);
        })->name('logout');
    });

Route::post('/sanctum/token', SanctumTokenController::class)->name('sanctum.token');

Route::get('/acts', [ActController::class, 'api_index'])
    ->name('acts.index');
Route::get('/acts/{act}', [ActController::class, 'api_public_show'])->name('acts.show');

Route::middleware('auth:sanctum')
    ->prefix('private')
    ->name('private.')
    ->group(function () {
        Route::prefix('acts')
            ->name('acts.')
            ->group(function () {
                Route::get('/', [ActController::class, 'api_index'])
                    ->name('index');
                Route::post('/', [ActController::class, 'api_store'])->name('store');
                Route::get('/mine', [ActController::class, 'api_mine'])->name('mine');
                Route::get('/{act}', [ActController::class, 'api_show'])->name('show');
            });

    });
