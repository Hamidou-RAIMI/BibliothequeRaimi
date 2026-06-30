<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
// IMPORTANT : importation des contrôleurs nécessaires pour gérer les ressources

use App\Http\Controllers\ReferenceController;
use App\Http\Controllers\CategorieController;

use App\Http\Controllers\StatisticsController;

// ============================================================================
// ROUTES PUBLIQUES (sans authentification)
// ============================================================================
// Ces routes sont accessibles à tous (inscription et connexion)
Route::post('/register', [AuthController::class, 'register']); // Inscription d'un utilisateur
Route::post('/login', [AuthController::class, 'login']);     // Connexion d'un utilisateur
Route::get('/public/categories', [CategorieController::class, 'index']); // Liste toutes les catégories
Route::get('/public/references', [ReferenceController::class, 'publicIndex']); // Liste toutes les références publiées

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
    // ROUTES POUR LES STATISTIQUES
    // ========================================================================
    Route::get('/statistics', [StatisticsController::class, 'index']);

    // ========================================================================
    // ROUTES POUR LA GESTION DES UTILISATEURS
    // ========================================================================
    // Gestion complète des utilisateurs (CRUD)
        require __DIR__.'/utilisateur.php';

    // ========================================================================
    // ROUTES POUR LA GESTION DES RÉFÉRENCES
    // ========================================================================
    // Gestion complète des références (livres, articles, thèses, etc.)

        require __DIR__.'/reference.php';
    
    // ========================================================================
    // ROUTES POUR LA GESTION DES CATÉGORIES
    // ========================================================================
    // Gestion complète des catégories
    
    require __DIR__.'/categorie.php';
    
    // ========================================================================
    // ROUTES POUR LA GESTION DES AUTEURS
    // ========================================================================
    // Gestion complète des auteurs

    require __DIR__.'/auteur.php';
    
    // ========================================================================
    // ROUTES POUR LA GESTION DES ÉDITEURS
    // ========================================================================
    // Gestion complète des éditeurs
    
    require __DIR__.'/editeur.php';
    
    // ========================================================================
    // ROUTES POUR LA GESTION DES DEMANDES DE DÉPÔT
    // ========================================================================
    // Gestion complète des demandes de dépôt

    require __DIR__.'/demandeRequest.php';
});
