<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Data\WeightDataController;
use App\Http\Controllers\FitbitAuthController;
use App\Http\Controllers\FitbitDataController;
use App\Http\Controllers\FitbitController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/weightdash', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/weightdash', [WeightDataController::class, 'show'])->name('weightdash.show');
    Route::get('/weightdash/data', [WeightDataController::class, 'index'])->name('weightdash.managedata');

    Route::get('/fitbitauth', [FitbitAuthController::class, 'fitbit_auth'])->name('fitbit.auth');
    Route::get('/fbredirect', [FitbitAuthController::class, 'fitbit_webhook_capture'])->name('fitbit.auth.success');

    Route::put('/weightdash/data/store', [FitbitDataController::class, 'storeWeightData'])->name('weightdash.storedata');
});

Route::middleware('auth:sanctum')->get('/api/querydata', [WeightDataController::class, 'get_weight_data'])->name('weightdash.query');


require __DIR__.'/auth.php';
