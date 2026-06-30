<?php

use App\Http\Controllers\CategorieController;
use Illuminate\Support\Facades\Route;



// ========================================================================
    // ROUTES POUR LA GESTION DES CATÉGORIES
    // ========================================================================
    // Gestion complète des catégories
Route::get('/categories', [CategorieController::class, 'index']);           // Liste toutes les catégories (triées par nom)
    Route::get('/categories/{id}', [CategorieController::class, 'show']);       // Affiche une catégorie spécifique
    Route::post('/categories', [CategorieController::class, 'store']);          // Crée une nouvelle catégorie
    Route::put('/categories/{id}', [CategorieController::class, 'update']);     // Modifie une catégorie
    Route::delete('/categories/{id}', [CategorieController::class, 'destroy']); // Supprime une catégorie