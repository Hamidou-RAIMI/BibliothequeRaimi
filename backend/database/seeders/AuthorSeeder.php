<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Author;

class AuthorSeeder extends Seeder
{
    /**
     * Exécute les seeders pour la table des auteurs
     * 
     * UTILITÉ : Crée des auteurs connus et réels pour les références documentaires
     * Cela rend le catalogue plus crédible et plus proche d'une vraie bibliothèque numérique.
     */
    public function run(): void
    {
        // Tableau des auteurs connus à créer
        $auteurs = [
            [
                'first_name' => 'Victor',
                'last_name' => 'Hugo',
                'biography' => 'Écrivain, poète et dramaturge français, figure majeur du romantisme. Auteur de "Les Misérables" et "Notre-Dame de Paris".',
                'nationality' => 'Française',
                'birth_date' => '1802-02-26',
                'death_date' => '1885-05-22',
            ],
            [
                'first_name' => 'Albert',
                'last_name' => 'Einstein',
                'biography' => 'Physicien théoricien allemand, connu pour la théorie de la relativité restreinte et générale. Prix Nobel de physique en 1921.',
                'nationality' => 'Allemande (Américaine naturalisée)',
                'birth_date' => '1879-03-14',
                'death_date' => '1955-04-18',
            ],
            [
                'first_name' => 'Simone',
                'last_name' => 'de Beauvoir',
                'biography' => 'Écrivaine, philosophe et féministe française. Auteur de "Le Deuxième Sexe", fondement du féminisme moderne.',
                'nationality' => 'Française',
                'birth_date' => '1908-01-09',
                'death_date' => '1986-04-14',
            ],
            [
                'first_name' => 'Jean-Paul',
                'last_name' => 'Sartre',
                'biography' => 'Philosophe, écrivain et dramaturge français, figure principale de l\'existentialisme.',
                'nationality' => 'Française',
                'birth_date' => '1905-06-21',
                'death_date' => '1980-04-15',
            ],
            [
                'first_name' => 'Marie',
                'last_name' => 'Curie',
                'biography' => 'Physicienne et chimiste polonaise-naturalisée française. Première femme prix Nobel, et la seule à avoir gagné dans deux sciences différentes.',
                'nationality' => 'Polonaise (Française naturalisée)',
                'birth_date' => '1867-11-07',
                'death_date' => '1934-07-04',
            ],
            [
                'first_name' => 'Charles',
                'last_name' => 'Darwin',
                'biography' => 'Naturaliste et géologue anglais, connu pour sa théorie de l\'évolution par sélection naturelle.',
                'nationality' => 'Anglaise',
                'birth_date' => '1809-02-12',
                'death_date' => '1882-04-19',
            ],
        ];

        // Crée chaque auteur dans la base de données
        foreach ($auteurs as $auteur) {
            Author::create($auteur);
        }

        // Ajoute 15 auteurs aléatoires supplémentaires avec le factory
        // Cela permet d'avoir un large choix d'auteurs pour les références
        Author::factory(15)->create();
    }
}
