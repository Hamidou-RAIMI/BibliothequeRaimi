<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Publisher;

class PublisherSeeder extends Seeder
{
    /**
     * Exécute les seeders pour la table des éditeurs
     * 
     * UTILITÉ : Crée des maisons d'édition connues pour les références documentaires
     * Cela complète les informations des livres et documents et rend le catalogue plus réaliste.
     */
    public function run(): void
    {
        // Tableau des éditeurs connus à créer
        $editeurs = [
            [
                'name' => 'Gallimard',
                'description' => 'Maison d\'édition française fondée en 1911, connue pour sa collection "La Pléiade".',
                'country' => 'France',
                'website' => 'https://www.gallimard.fr',
            ],
            [
                'name' => 'Flammarion',
                'description' => 'Groupe éditorial français fondé en 1876, publie des ouvrages dans de nombreux domaines.',
                'country' => 'France',
                'website' => 'https://www.flammarion.com',
            ],
            [
                'name' => 'Éditions du Seuil',
                'description' => 'Maison d\'édition française indépendante fondée en 1935.',
                'country' => 'France',
                'website' => 'https://www.seuil.com',
            ],
            [
                'name' => 'Presses Universitaires de France (PUF)',
                'description' => 'Maison d\'édition française spécialisée dans les ouvrages universitaires et scientifiques.',
                'country' => 'France',
                'website' => 'https://www.puf.com',
            ],
            [
                'name' => 'Dunod',
                'description' => 'Éditeur français spécialisé dans les sciences, techniques et pédagogie.',
                'country' => 'France',
                'website' => 'https://www.dunod.com',
            ],
            [
                'name' => 'Oxford University Press',
                'description' => 'Maison d\'édition universitaire britannique, la plus grande au monde.',
                'country' => 'Royaume-Uni',
                'website' => 'https://global.oup.com',
            ],
        ];

        // Crée chaque éditeur dans la base de données
        foreach ($editeurs as $editeur) {
            Publisher::create($editeur);
        }

        // Ajoute 10 éditeurs aléatoires supplémentaires avec le factory
        // Cela augmente la variété des sources d'édition
        Publisher::factory(10)->create();
    }
}
