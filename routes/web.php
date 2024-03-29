<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\InventoryDraftController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\PartController;
use App\Http\Controllers\ProjectBuildController;
use App\Http\Controllers\ProjectBuildPartController;
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
    Route::post('import-parts-document', [PartController::class, 'importPartsDocument'])->name('import-parts-document');

    Route::controller(LocationController::class)->group(function () {
        Route::post('locations', 'store')->name('locations.store');
        Route::put('locations/{location}', 'update')->name('locations.update');
        Route::delete('locations/{location}', 'destroy')->name('locations.destroy');

        Route::get('inventories/locations', 'index')->name('locations.index');
        Route::get('inventories/locations/{location}/edit', 'edit')->name('locations.edit');
    });

    Route::post('sku-part', [InventoryController::class, 'addBySKU'])->name('sku-part');
    Route::get('inventories/drafts', [InventoryDraftController::class, 'index'])->name('inventory-drafts.index');
    Route::post('inventories/create/{inventory_draft}', [InventoryController::class, 'store'])->name('inventories.store');
    Route::resource('inventories', InventoryController::class)->except(['store']);
    Route::get('inventories/create/{inventory_draft}', [InventoryDraftController::class, 'create'])->name('inventory-drafts.create');
    Route::put('inventories/{inventory}/location', [InventoryController::class, 'updateLocation'])->name('inventories.location.update');
    Route::post('inventory-drafts/{location}', [InventoryDraftController::class, 'store'])->name('inventory-drafts.store');
    Route::delete('inventory-drafts/{inventory_draft}', [InventoryDraftController::class, 'destroy'])->name('inventory-drafts.destroy');

    Route::controller(ProjectPartController::class)->group(function () {
        Route::get('projects/{project}/bom', 'index')->name('project-parts.index');
        Route::post('projects/{project}/bom', 'store')->name('project-parts.store');
        Route::put('bom/{project_part}', 'update')->name('project-parts.update');
        Route::delete('bom/{project_part}', 'destroy')->name('project-parts.destroy');
    });

    Route::controller(ProjectBuildController::class)->group(function () {
        Route::prefix('projects/{project}/builds')->group(function () {
            Route::get('/', 'index')->name('project-builds.index');
            Route::post('/', 'store')->name('project-builds.store');
            Route::get('/create', 'create')->name('project-builds.create');
            Route::get('/{project_build}', 'edit')->name('project-builds.edit');
            Route::get('/{project_build}/review', 'show')->name('project-builds.show');
        });

        Route::prefix('project-builds')->group(function () {
            Route::post('/{project_build}', 'build')->name('project-builds.build');
            Route::post('/{project_build}/undo', 'undoBuild')->name('project-builds.undo');
            Route::put('/{project_build}/update-priority', 'updateBuildSelectionPriority')->name('project-builds.update-priority');
            Route::delete('/{project_build}', 'destroy')->name('project-builds.destroy');
        });
    });

    Route::controller(ProjectBuildPartController::class)->group(function () {
        Route::prefix('projects/{project}/builds/{project_build}/parts')->group(function () {
            Route::post('/create', 'store')->name('project-build-parts.store');
            Route::get('/{project_part}', 'index')->name('project-build-parts.index');
            Route::get('/{project_part}/draft', 'getProjectBuildPartsDraft')->name('project-build-parts.draft');
            Route::delete('/{project_part}/inventory/{inventory}', 'destroy')->name('project-build-parts.destroy');
        });
    });

});

require __DIR__ . '/auth.php';
