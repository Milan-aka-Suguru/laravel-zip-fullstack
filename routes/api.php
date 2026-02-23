<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\ExportController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CountyController;
use App\Http\Controllers\TownController;
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/counties', [CountyController::class, 'store']);
    Route::put('/counties/{id}', [CountyController::class, 'update']);
    Route::delete('/counties/{id}', [CountyController::class, 'destroy']);
});
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/towns', [TownController::class, 'store']);
    Route::put('/towns/{id}', [TownController::class, 'update']);
    Route::delete('/towns/{id}', [TownController::class, 'destroy']);
});
Route::get('/towns', [TownController::class, 'index']);
Route::get('/counties', [CountyController::class, 'index']);
Route::get('/towns/show', [TownController::class, 'show']);
Route::get('/counties/show', [CountyController::class, 'show']);
Route::get('/towns/{id}', [TownController::class, 'show']);
Route::get('/counties/{id}', [CountyController::class, 'show']);
Route::post('/users/login',[UserController::class, 'login']);
Route::get('/users',[UserController::class, 'index'])->middleware('auth:sanctum');
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::post('/users/logout', function (Request $request) {
    $request->user()->tokens()->delete();
    return response()->json(['message' => 'Logged out']);
})->middleware('auth:sanctum');
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
->name('logout');

// Export routes
Route::post('/export/counties/csv', [ExportController::class, 'exportCountiesCsv']);
Route::post('/export/counties/pdf', [ExportController::class, 'exportCountiesPdf']);
Route::post('/export/towns/csv', [ExportController::class, 'exportTownsCsv']);
Route::post('/export/towns/pdf', [ExportController::class, 'exportTownsPdf']);
