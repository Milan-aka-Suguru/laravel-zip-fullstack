<?php

use App\Http\Controllers\CountyController;
use App\Http\Controllers\TownController;
use App\Models\Counties;
use App\Models\Towns;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Index', [
        'counties' => Counties::query()->orderBy('name')->get(['id', 'name']),
        'towns' => Towns::query()
            ->with('county:id,name')
            ->orderBy('name')
            ->get(['id', 'name', 'zip_code', 'county_id']),
    ]);
})->name("home");

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return redirect()->route('home');
    })->name('dashboard');

    Route::post('/counties', [CountyController::class, 'store'])->name('counties.store');
    Route::put('/counties/{id}', [CountyController::class, 'update'])->name('counties.update');
    Route::delete('/counties/{id}', [CountyController::class, 'destroy'])->name('counties.destroy');

    Route::post('/towns', [TownController::class, 'store'])->name('towns.store');
    Route::put('/towns/{id}', [TownController::class, 'update'])->name('towns.update');
    Route::delete('/towns/{id}', [TownController::class, 'destroy'])->name('towns.destroy');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
