<?php

use App\Http\Controllers\PartCategoryController;
use App\Http\Controllers\PartController;
use App\Http\Controllers\PartInventoryController;
use App\Http\Controllers\PedalTypeController;
use App\Http\Controllers\SourceController;
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

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('pedal-types', PedalTypeController::class);
    Route::resource('part-categories', PartCategoryController::class);
    Route::resource('parts', PartController::class);
    Route::get('sku-part', [PartController::class, 'findBySKU'])->name('sku-part');
    Route::resource('part-sources', SourceController::class);
    Route::resource('part-inventories', PartInventoryController::class);
    Route::post('tayda-pdf-to-products', [PartController::class, 'pdfToProducts'])->name('tayda-pdf-to-products');
});

require __DIR__ . '/auth.php';
