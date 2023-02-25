<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\InventoryDraftController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\PartController;
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
    Route::put('projects/{project}/description', [ProjectController::class, 'updateDescription'])->name('projects-description.update');
    Route::post('sku-part', [InventoryController::class, 'addBySKU'])->name('sku-part');

    Route::get('inventories/drafts', [InventoryDraftController::class, 'index'])->name('inventory-drafts.index');
    Route::delete('inventory-drafts/{inventory_draft}', [InventoryDraftController::class, 'destroy'])->name('inventory-drafts.destroy');

    Route::get('inventories/locations', [LocationController::class, 'index'])->name('locations.index');
    Route::get('inventories/locations/{location}/edit', [LocationController::class, 'edit'])->name('locations.edit');

    Route::post('inventories/create/{inventory_draft}', [InventoryController::class, 'store'])->name('inventories.store');
    Route::resource('inventories', InventoryController::class)->except(['store']);
    Route::get('inventories/create/{inventory_draft}', [InventoryDraftController::class, 'create'])->name('inventory-drafts.create');
    Route::put('inventories/{inventory}/location', [InventoryController::class, 'updateLocation'])->name('inventories.location.update');
    Route::post('inventory-drafts/{location}', [InventoryDraftController::class, 'store'])->name('inventory-drafts.store');

    Route::post('tayda-pdf-to-products', [PartController::class, 'pdfToProducts'])->name('tayda-pdf-to-products');
    Route::get('projects/{project}/bom', [ProjectPartController::class, 'index'])->name('project-parts.index');
    Route::post('projects/{project}/bom', [ProjectPartController::class, 'store'])->name('project-parts.store');
    Route::put('bom/{project_part}', [ProjectPartController::class, 'update'])->name('project-parts.update');
    Route::delete('bom/{project_part}', [ProjectPartController::class, 'destroy'])->name('project-parts.destroy');

    Route::post('locations', [LocationController::class, 'store'])->name('locations.store');
    Route::put('locations/{location}', [LocationController::class, 'update'])->name('locations.update');
    Route::delete('locations/{location}', [LocationController::class, 'destroy'])->name('locations.destroy');
});

require __DIR__ . '/auth.php';
