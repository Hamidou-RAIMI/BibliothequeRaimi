<?php
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;


    // ========================================================================
    // ROUTES POUR LA GESTION DES UTILISATEURS
    // ========================================================================
    // Gestion complète des utilisateurs (CRUD)


    Route::get('/users', [UserController::class, 'index']);                     // Liste tous les utilisateurs
    Route::get('/users/managers', [UserController::class, 'getManagers']);      // Liste tous les responsables de demande
    Route::get('/users/archived', [UserController::class, 'archived']);         // Liste tous les utilisateurs archivés
    Route::get('/users/{id}', [UserController::class, 'show']);                 // Affiche un utilisateur spécifique
    Route::post('/users', [UserController::class, 'store']);                    // Crée un nouveau utilisateur
    Route::put('/users/{id}', [UserController::class, 'update']);               // Modifie un utilisateur
    Route::delete('/users/{id}', [UserController::class, 'destroy']);           // Archive un utilisateur
    Route::post('/users/{id}/restore', [UserController::class, 'restore']);     // Désarchive un utilisateur
    Route::post('/users/{id}/toggle-status', [UserController::class, 'toggleStatus']); // Active/désactive un utilisateur