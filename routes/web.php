<?php

use App\Http\Controllers\ImportIngredientsController;
use App\Http\Controllers\IngredientController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RecipeController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        // 'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::post('/ingredients/quick-add', [IngredientController::class, 'quickAdd'])
    ->name('ingredients.quick-add')
    ->middleware(['auth']);
Route::resource('ingredients', IngredientController::class);
Route::resource('suppliers', SupplierController::class);
Route::resource('recipes', RecipeController::class);


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::post('/ingredients/import', [ImportIngredientsController::class, 'import'])
    ->name('ingredients.import')
    ->middleware(['auth']);

require __DIR__.'/auth.php';
