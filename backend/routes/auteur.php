<?php

use App\Http\Controllers\AuthorController;
use Illuminate\Support\Facades\Route;

// ========================================================================
    // ROUTES POUR LA GESTION DES AUTEURS
    // ========================================================================
    // Gestion complète des auteurs
    Route::get('/authors', [AuthorController::class, 'index']);                 // Liste tous les auteurs (triés par nom)
    Route::get('/authors/{id}', [AuthorController::class, 'show']);             // Affiche un auteur spécifique
    Route::post('/authors', [AuthorController::class, 'store']);                // Crée un nouveau auteur
    Route::put('/authors/{id}', [AuthorController::class, 'update']);           // Modifie un auteur
    Route::delete('/authors/{id}', [AuthorController::class, 'destroy']);       // Supprime un auteur