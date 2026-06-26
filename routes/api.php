<?php

use App\Http\Controllers\ActController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;

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

Route::post('/sanctum/token', function (Request $request) {
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
        'device_name' => 'required',
    ]);

    $user = User::where('email', $request->email)->first();

    if (! $user || ! Hash::check($request->password, $user->password)) {
        throw ValidationException::withMessages([
            'email' => ['The provided credentials are incorrect.'],
        ]);
    }

    return ['token' => $user->createToken($request->device_name)->plainTextToken];
})->name('sanctum.token');

Route::get('/acts', [ActController::class, 'index']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/acts', [ActController::class, 'store']);
});
