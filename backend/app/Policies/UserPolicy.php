<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    /**
     * =========================================================================
     * VÉRIFIER SI L'UTILISATEUR PEUT VOIR LA LISTE DES UTILISATEURS
     * =========================================================================
     * 
     * RÈGLE : Seuls les ADMINISTRATEURS et les RESPONSABLES RH peuvent accéder à la liste
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'responsable_rh']);
    }

    /**
     * =========================================================================
     * VÉRIFIER SI L'UTILISATEUR PEUT VOIR UN UTILISATEUR SPÉCIFIQUE
     * =========================================================================
     * 
     * RÈGLE : 
     * - Admin/RH peuvent voir n'importe qui
     * - Un utilisateur peut voir SON PROPRE profil
     */
    public function view(User $user, User $model): bool
    {
        return in_array($user->role, ['admin', 'responsable_rh']) || $user->id === $model->id;
    }

    /**
     * =========================================================================
     * VÉRIFIER SI L'UTILISATEUR PEUT CRÉER UN NOUVEL UTILISATEUR
     * =========================================================================
     * 
     * RÈGLE : Seuls les ADMIN et RESPONSABLE RH peuvent créer des comptes
     */
    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'responsable_rh']);
    }

    /**
     * =========================================================================
     * VÉRIFIER SI L'UTILISATEUR PEUT MODIFIER UN UTILISATEUR
     * =========================================================================
     * 
     * RÈGLE : 
     * - Admin/RH peuvent modifier n'importe qui
     * - Un utilisateur peut modifier SON PROPRE profil (mais pas son rôle !)
     */
    public function update(User $user, User $model): bool
    {
        return in_array($user->role, ['admin', 'responsable_rh']) || $user->id === $model->id;
    }

    /**
     * =========================================================================
     * VÉRIFIER SI L'UTILISATEUR PEUT SUPPRIMER UN UTILISATEUR
     * =========================================================================
     * 
     * RÈGLE : Seuls les ADMIN peuvent supprimer des comptes (plus sécurisé)
     */
    public function delete(User $user, User $model): bool
    {
        return $user->role === 'admin';
    }

    /**
     * =========================================================================
     * VÉRIFIER SI L'UTILISATEUR PEUT CHANGER LE STATUT (ACTIVER/DÉSACTIVER)
     * =========================================================================
     * 
     * RÈGLE : Admin et RH peuvent changer le statut des utilisateurs
     */
    public function toggleStatus(User $user): bool
    {
        return in_array($user->role, ['admin', 'responsable_rh']);
    }
}

