<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
// IMPORTANT : importation des contrôleurs nécessaires pour gérer les ressources
use App\Http\Controllers\UserController;
use App\Http\Controllers\ReferenceController;
use App\Http\Controllers\CategorieController;
use App\Http\Controllers\PublisherController;
use App\Http\Controllers\AuthorController;

// ============================================================================
// ROUTES PUBLIQUES (sans authentification)
// ============================================================================
// Ces routes sont accessibles à tous (inscription et connexion)
Route::post('/register', [AuthController::class, 'register']); // Inscription d'un utilisateur
Route::post('/login', [AuthController::class, 'login']);     // Connexion d'un utilisateur

// ============================================================================
// ROUTES PROTÉGÉES (nécessite une authentification via Sanctum)
// ============================================================================
// Toutes les routes dans ce groupe nécessitent que l'utilisateur soit connecté
Route::middleware('auth:sanctum')->group(function () {
    // ========================================================================
    // ROUTES UTILISATEUR COURANT
    // ========================================================================
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    
    Route::post('/logout', [AuthController::class, 'logout']); // Déconnexion de l'utilisateur
    Route::get('/me', [AuthController::class, 'me']);         // Récupérer les infos de l'utilisateur connecté

    // ========================================================================
    // ROUTES POUR LA GESTION DES UTILISATEURS
    // ========================================================================
    // Gestion complète des utilisateurs (CRUD)
    Route::get('/users', [UserController::class, 'index']);                     // Liste tous les utilisateurs
    Route::get('/users/{id}', [UserController::class, 'show']);                 // Affiche un utilisateur spécifique
    Route::post('/users', [UserController::class, 'store']);                    // Crée un nouveau utilisateur
    Route::put('/users/{id}', [UserController::class, 'update']);               // Modifie un utilisateur
    Route::delete('/users/{id}', [UserController::class, 'destroy']);           // Supprime un utilisateur
    Route::post('/users/{id}/toggle-status', [UserController::class, 'toggleStatus']); // Active/désactive un utilisateur

    // ========================================================================
    // ROUTES POUR LA GESTION DES RÉFÉRENCES
    // ========================================================================
    // Gestion complète des références (livres, articles, thèses, etc.)
    Route::get('/references', [ReferenceController::class, 'index']);           // Liste toutes les références
    Route::get('/references/{id}', [ReferenceController::class, 'show']);       // Affiche une référence spécifique
    Route::post('/references', [ReferenceController::class, 'store']);          // Crée une nouvelle référence
    Route::put('/references/{id}', [ReferenceController::class, 'update']);     // Modifie une référence
    Route::delete('/references/{id}', [ReferenceController::class, 'destroy']); // Supprime une référence
    Route::post('/references/{id}/toggle-status', [ReferenceController::class, 'toggleStatus']); // Change le statut (brouillon/publié/archivé)
    
    // ========================================================================
    // ROUTES POUR LA GESTION DES CATÉGORIES
    // ========================================================================
    Route::get('/categories', [CategorieController::class, 'index']);           // Liste toutes les catégories
    
    // ========================================================================
    // ROUTES POUR LA GESTION DES AUTEURS
    // ========================================================================
    // Gestion complète des auteurs
    Route::get('/authors', [AuthorController::class, 'index']);                 // Liste tous les auteurs (triés par nom)
    Route::get('/authors/{id}', [AuthorController::class, 'show']);             // Affiche un auteur spécifique
    Route::post('/authors', [AuthorController::class, 'store']);                // Crée un nouveau auteur
    Route::put('/authors/{id}', [AuthorController::class, 'update']);           // Modifie un auteur
    Route::delete('/authors/{id}', [AuthorController::class, 'destroy']);       // Supprime un auteur
    
    // ========================================================================
    // ROUTES POUR LA GESTION DES ÉDITEURS
    // ========================================================================
    // Gestion complète des éditeurs
    Route::get('/publishers', [PublisherController::class, 'index']);           // Liste tous les éditeurs (triés par nom)
    Route::get('/publishers/{id}', [PublisherController::class, 'show']);       // Affiche un éditeur spécifique
    Route::post('/publishers', [PublisherController::class, 'store']);          // Crée un nouveau éditeur
    Route::put('/publishers/{id}', [PublisherController::class, 'update']);     // Modifie un éditeur
    Route::delete('/publishers/{id}', [PublisherController::class, 'destroy']); // Supprime un éditeur
});
