<?php

use App\Http\Controllers\DepositeRequestController;
use Illuminate\Support\Facades\Route;

// ========================================================================
    // ROUTES POUR LA GESTION DES DEMANDES DE DÉPÔT
    // ========================================================================
    // Gestion complète des demandes de dépôt
    Route::get('/deposite-requests', [DepositeRequestController::class, 'index']);                  // Liste toutes les demandes de dépôt (admin)
    Route::get('/my-deposite-requests', [DepositeRequestController::class, 'myRequests']);           // Liste les demandes de l'utilisateur connecté
    Route::get('/deposite-requests/{id}', [DepositeRequestController::class, 'show']);              // Affiche une demande de dépôt spécifique
    Route::post('/deposite-requests', [DepositeRequestController::class, 'store']);                 // Crée une nouvelle demande de dépôt
    Route::put('/deposite-requests/{id}', [DepositeRequestController::class, 'update']);            // Modifie une demande de dépôt
    Route::post('/deposite-requests/{id}/assign', [DepositeRequestController::class, 'assign']);    // Affecte une demande à un responsable
    Route::post('/deposite-requests/{id}/reassign', [DepositeRequestController::class, 'reassign']);// Réaffecte une demande à un autre responsable
    Route::post('/deposite-requests/{id}/review', [DepositeRequestController::class, 'submitReview']);// Le responsable soumet son avis
    Route::post('/deposite-requests/{id}/publish', [DepositeRequestController::class, 'publish']);  // L'admin publie la demande
    Route::post('/deposite-requests/{id}/reject', [DepositeRequestController::class, 'reject']);    // L'admin rejette la demande
    Route::delete('/deposite-requests/{id}', [DepositeRequestController::class, 'destroy']);         // Supprimer une demande de dépôt