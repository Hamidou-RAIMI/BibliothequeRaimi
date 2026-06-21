<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Exécute les seeders pour la table des utilisateurs
     * 
     * UTILITÉ : Crée des comptes de test pour tous les profils d'utilisateurs
     * (administrateur, responsable RH, responsable demande, utilisateur inscrit)
     * Ces comptes permettent de tester immédiatement toutes les fonctionnalités
     * de l'application sans avoir à créer manuellement des comptes.
     * 
     * MOT DE PASSE PAR DÉFAUT POUR TOUS LES COMPTES : password
     */
    public function run(): void
    {
        // 1. Création du compte Administrateur
        // Ce compte a TOUS les droits (gestion comptes, validation finale, publication, etc.)
        User::create([
            'first_name' => 'Admin',
            'last_name' => 'System',
            'email' => 'admin@bibliotheque.com',
            'phone' => '0123456789',
            'password' => Hash::make('password'), // Hash sécurisé du mot de passe
            'role' => 'admin',
            'status' => 'active',
            'email_verified_at' => now(), // Marque l'email comme vérifié
        ]);

        // 2. Création du compte Responsable RH
        // Ce compte gère les comptes utilisateurs (création, modification, suppression)
        User::create([
            'first_name' => 'Marie',
            'last_name' => 'Dupont',
            'email' => 'rh@bibliotheque.com',
            'phone' => '0987654321',
            'password' => Hash::make('password'),
            'role' => 'responsable_rh',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        // 3. Création du compte Responsable Chargé de la Gestion des Demandes
        // Ce compte vérifie les demandes de dépôt de nouvelles références
        User::create([
            'first_name' => 'Paul',
            'last_name' => 'Martin',
            'email' => 'responsable.demande@bibliotheque.com',
            'phone' => '0112233445',
            'password' => Hash::make('password'),
            'role' => 'responsable_demande', // Rôle CORRIGÉ !
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        // 4. Création d'un compte Utilisateur Inscrit classique
        // Ce compte peut consulter, lire, télécharger et proposer des dépôts
        User::create([
            'first_name' => 'Sophie',
            'last_name' => 'Durand',
            'email' => 'sophie.durand@example.com',
            'phone' => '0612345678',
            'password' => Hash::make('password'),
            'role' => 'user',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        // 5. Création de 10 utilisateurs aléatoires supplémentaires avec le factory
        // Cela permet d'avoir une base de données plus fournie pour les tests
        User::factory(10)->create();
    }
}
