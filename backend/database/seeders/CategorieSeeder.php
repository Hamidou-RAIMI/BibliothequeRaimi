<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Categorie;
use Illuminate\Support\Str;

class CategorieSeeder extends Seeder
{
    /**
     * Exécute les seeders pour la table des catégories
     * 
     * UTILITÉ : Crée des catégories thématiques pour classifier les références documentaires
     * Cela permet aux utilisateurs de naviguer plus facilement dans le catalogue
     * et de filtrer les recherches par domaine.
     */
    public function run(): void
    {
        // Tableau des catégories  avec leur nom et description
        $categories = [
            [
                'name' => 'Informatique',
                'description' => 'Livres, articles et documents sur l\'informatique, la programmation, les réseaux et les technologies de l\'information.'
            ],
            [
                'name' => 'Droit',
                'description' => 'Ressources juridiques : droit civil, pénal, commercial, constitutionnel et international.'
            ],
            [
                'name' => 'Médecine',
                'description' => 'Documents sur la santé, la médecine générale, les spécialités médicales et les sciences biomédicales.'
            ],
            [
                'name' => 'Sciences Humaines',
                'description' => 'Philosophie, histoire, sociologie, psychologie, anthropologie et sciences politiques.'
            ],
            [
                'name' => 'Sciences Exactes',
                'description' => 'Mathématiques, physique, chimie, biologie, géologie et astronomie.'
            ],
            [
                'name' => 'Littérature',
                'description' => 'Romans, poésie, théâtre, essais littéraires et œuvres de fiction.'
            ],
            [
                'name' => 'Économie & Gestion',
                'description' => 'Économie, finance, gestion d\'entreprise, marketing et comptabilité.'
            ],
            [
                'name' => 'Arts & Culture',
                'description' => 'Beaux-arts, musique, cinéma, théâtre, histoire de l\'art et patrimoine culturel.'
            ]
        ];

        // Parcourt chaque catégorie et la crée dans la base de données si elle n'existe pas déjà
        foreach ($categories as $categorie) {
            Categorie::firstOrCreate(
                ['name' => $categorie['name']],
                [
                    'slug' => Str::slug($categorie['name']), // Génère un slug URL-friendly (ex: "informatique" pour "Informatique")
                    'description' => $categorie['description'],
                    'status' => 'active' // Toutes les catégories sont activées par défaut
                ]
            );
        }

        // Vérifie le nombre de catégories existantes avant d'ajouter les 5 aléatoires
        $nbCategoriesExistantes = Categorie::count();
        if ($nbCategoriesExistantes < 13) {
            // Ajoute 5 catégories aléatoires supplémentaires avec le factory
            // Cela donne encore plus de variété au catalogue
            Categorie::factory(5)->create();
        }
    }
}
