<?php

use App\Http\Controllers\ReferenceController;
use Illuminate\Support\Facades\Route;

// ========================================================================
    // ROUTES POUR LA GESTION DES RÉFÉRENCES
    // ========================================================================
    // Gestion complète des références (livres, articles, thèses, etc.)
    Route::get('/references', [ReferenceController::class, 'index']);           // Liste toutes les références
    Route::get('/references/archived', [ReferenceController::class, 'archived']); // Liste toutes les références archivées
    Route::get('/references/{id}', [ReferenceController::class, 'show']);       // Affiche une référence spécifique
    Route::post('/references', [ReferenceController::class, 'store']);          // Crée une nouvelle référence
    Route::put('/references/{id}', [ReferenceController::class, 'update']);     // Modifie une référence
    Route::post('/references/{id}/archive', [ReferenceController::class, 'archive']); // Archive une référence
    Route::post('/references/{id}/restore', [ReferenceController::class, 'restore']); // Restaure une référence archivée
    Route::post('/references/{id}/toggle-status', [ReferenceController::class, 'toggleStatus']); // Change le statut (brouillon/publié)
    Route::get('/references/{id}/download', [ReferenceController::class, 'download']); // Télécharge le PDF et incrémente le compteur