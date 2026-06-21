<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
// IMPORTANT : n'oublie pas d'importer le UserController !
use App\Http\Controllers\UserController;

// Routes publiques (sans authentification)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Routes protégées (avec authentification Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // =========================================================================
    // ROUTES POUR LA GESTION DES UTILISATEURS
    // =========================================================================
    // Ces routes ne sont accessibles qu'aux utilisateurs connectés
    // Plus tard, on ajoutera une policy pour restreindre à admin/responsable_rh
    Route::get('/users', [UserController::class, 'index']);           // Récupérer la liste
    Route::get('/users/{id}', [UserController::class, 'show']);       // Récupérer un utilisateur
    Route::post('/users', [UserController::class, 'store']);            // Créer un utilisateur
    Route::put('/users/{id}', [UserController::class, 'update']);         // Modifier un utilisateur
    Route::delete('/users/{id}', [UserController::class, 'destroy']); // Supprimer un utilisateur
    Route::post('/users/{id}/toggle-status', [UserController::class, 'toggleStatus']); // Bascule statut
});

