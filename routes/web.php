<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\PartController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectPartController;
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
    Route::resource('categories', CategoryController::class);
    Route::resource('parts', PartController::class);
    Route::resource('projects', ProjectController::class);
    Route::put('projects/{project}/description',  [ProjectController::class, 'updateDescription'])->name('projects-description.update');
    Route::get('sku-part', [PartController::class, 'findBySKU'])->name('sku-part');
    Route::resource('inventories', InventoryController::class);
    Route::post('tayda-pdf-to-products', [PartController::class, 'pdfToProducts'])->name('tayda-pdf-to-products');
    Route::get('projects/{project}/bom', [ProjectPartController::class, 'index'])->name('project-parts.index');
    Route::post('projects/{project}/bom', [ProjectPartController::class, 'store'])->name('project-parts.store');
    Route::put('bom/{project_part}', [ProjectPartController::class, 'update'])->name('project-parts.update');
    Route::delete('bom/{project_part}', [ProjectPartController::class, 'destroy'])->name('project-parts.destroy');
});

require __DIR__ . '/auth.php';
