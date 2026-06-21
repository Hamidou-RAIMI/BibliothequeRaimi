<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Exécute tous les seeders de l'application
     * 
     * IMPORTANT : L'ORDRE DES SEEDERS EST CRUCIAL !
     * On doit d'abord créer les éléments qui ne dépendent de personne
     * (Utilisateurs, Catégories, Auteurs, Éditeurs), puis les éléments qui en dépendent
     * (Références), et enfin les demandes de dépôt.
     */
    public function run(): void
    {
        $this->call([
            // 1. D'abord les utilisateurs (tous les rôles)
            UserSeeder::class,
            
            // 2. Puis les catégories, auteurs et éditeurs (pas de dépendances entre eux)
            CategorieSeeder::class,
            AuthorSeeder::class,
            PublisherSeeder::class,
            
            // 3. Ensuite les références (dépendent des catégories, éditeurs et utilisateurs)
            ReferenceSeeder::class,
            
            // 4. Enfin les demandes de dépôt (dépendent des utilisateurs)
            DepositeRequestSeeder::class,
        ]);
    }
}
