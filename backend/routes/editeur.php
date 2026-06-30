<?php

use App\Http\Controllers\PublisherController;
use Illuminate\Support\Facades\Route;


    // ========================================================================
    // ROUTES POUR LA GESTION DES ÉDITEURS
    // ========================================================================
    // Gestion complète des éditeurs

    Route::get('/publishers', [PublisherController::class, 'index']);           // Liste tous les éditeurs (triés par nom)
    Route::get('/publishers/{id}', [PublisherController::class, 'show']);       // Affiche un éditeur spécifique
    Route::post('/publishers', [PublisherController::class, 'store']);          // Crée un nouveau éditeur
    Route::put('/publishers/{id}', [PublisherController::class, 'update']);     // Modifie un éditeur
    Route::delete('/publishers/{id}', [PublisherController::class, 'destroy']); // Supprime un éditeur
    