<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PreferenceController;
use App\Http\Controllers\FridgeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MealPlanController;
use App\Http\Controllers\RecipeController;
use App\Http\Controllers\AdminController;

// Public routes
Route::get('/', function () {
    // If user is authenticated, redirect to dashboard
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return view('welcome');
})->name('home');

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

// Google OAuth routes
Route::get('/auth/google', [AuthController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected routes
Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Fridge Management
    Route::prefix('fridge')->name('fridge.')->group(function () {
        Route::get('/', [FridgeController::class, 'index'])->name('index');
        Route::get('/create', [FridgeController::class, 'create'])->name('create');
        Route::post('/', [FridgeController::class, 'store'])->name('store');
        Route::get('/{fridgeItem}/edit', [FridgeController::class, 'edit'])->name('edit');
        Route::put('/{fridgeItem}', [FridgeController::class, 'update'])->name('update');
        Route::delete('/{fridgeItem}', [FridgeController::class, 'destroy'])->name('destroy');

        // AI Scan
        Route::get('/scan', [FridgeController::class, 'scan'])->name('scan');
        Route::post('/upload-photo', [FridgeController::class, 'uploadPhoto'])->name('upload-photo');
        Route::post('/store-batch', [FridgeController::class, 'storeBatch'])->name('store-batch');
    });

    // Meal Plans
    Route::prefix('meal-plans')->name('meal-plans.')->group(function () {
        Route::get('/', [MealPlanController::class, 'index'])->name('index');
        Route::get('/generate', [MealPlanController::class, 'create'])->name('create');
        Route::post('/generate', [MealPlanController::class, 'generate'])->name('generate');
        Route::get('/{mealPlan}', [MealPlanController::class, 'show'])->name('show');
        Route::delete('/{mealPlan}', [MealPlanController::class, 'destroy'])->name('destroy');
    });

    // Recipes
    Route::prefix('recipes')->name('recipes.')->group(function () {
        Route::get('/', [RecipeController::class, 'index'])->name('index');
        Route::get('/random', [RecipeController::class, 'random'])->name('random');
        Route::get('/{recipeId}', [RecipeController::class, 'show'])->name('show');
    });

    // User Preferences
    Route::get('/preferences', [PreferenceController::class, 'show'])->name('preferences.show');
    Route::put('/preferences', [PreferenceController::class, 'update'])->name('preferences.update');
});

// Admin routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/settings', [AdminController::class, 'settings'])->name('settings');

    // Vertex AI
    Route::post('/vertex-ai', [AdminController::class, 'updateVertexAI'])->name('vertex-ai.update');
    Route::post('/vertex-ai/test', [AdminController::class, 'testVertexAI'])->name('vertex-ai.test');

    // Spoonacular
    Route::post('/spoonacular', [AdminController::class, 'updateSpoonacular'])->name('spoonacular.update');
    Route::post('/spoonacular/test', [AdminController::class, 'testSpoonacular'])->name('spoonacular.test');
});
