<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Reference;
use App\Models\Categorie;
use App\Models\Publisher;
use App\Models\User;
use App\Models\Author;
use Illuminate\Support\Facades\DB;

class ReferenceSeeder extends Seeder
{
    /**
     * Exécute les seeders pour la table des références
     * 
     * UTILITÉ : Crée des références documentaires réalistes avec tous leurs éléments associés
     * (catégorie, éditeur, auteurs, mots-clés). C'est le cœur du catalogue de la bibliothèque numérique.
     */
    public function run(): void
    {
        // Récupère des éléments existants pour les associer aux références
        $categories = Categorie::all();
        $publishers = Publisher::all();
        $users = User::all();
        $authors = Author::all();

        // if ($categories->isEmpty()) {
        //     throw new \Exception('Aucune catégorie trouvée. Exécute CategorieSeeder d\'abord.');
        //     }
        // Si pas assez d'éléments, on utilise le factory pour en créer
        if ($categories->isEmpty()) $categories = Categorie::factory(5)->create();
        if ($publishers->isEmpty()) $publishers = Publisher::factory(5)->create();
        if ($users->isEmpty()) $users = User::factory(5)->create();
        if ($authors->isEmpty()) $authors = Author::factory(10)->create();

        // Tableau de références réalistes à créer
        $references = [
            [
                'title' => 'Les Misérables',
                'subtitle' => 'Roman social et historique',
                'abstract' => 'Un roman majeur de Victor Hugo qui raconte l\'histoire de Jean Valjean, un ancien forçat qui cherche à se réinsérer dans la société.',
                'isbn' => '9782070409352',
                'publication_year' => 1862,
                'language' => 'fr',
                'document_type' => 'livre',
                'pages' => 1500,
                'status' => 'published',
                'cover_image' => 'covers/les_miserables.jpg',
            ],
            [
                'title' => 'La Relativité',
                'subtitle' => 'Exposé de la théorie de la relativité restreinte et générale',
                'abstract' => 'L\'ouvrage fondateur d\'Albert Einstein qui présente sa théorie révolutionnaire de l\'espace, du temps et de la gravitation.',
                'isbn' => '9782070401842',
                'publication_year' => 1916,
                'language' => 'fr',
                'document_type' => 'livre',
                'pages' => 300,
                'status' => 'published',
                
            ],
            [
                'title' => 'Le Deuxième Sexe',
                'subtitle' => 'Essai sur la condition féminine',
                'abstract' => 'Un ouvrage fondateur du féminisme moderne, dans lequel Simone de Beauvoir analyse la situation des femmes dans la société.',
                'isbn' => '9782070402461',
                'publication_year' => 1949,
                'language' => 'fr',
                'document_type' => 'livre',
                'pages' => 800,
                'status' => 'published',
            ],
            [
                'title' => 'L\'Être et le Néant',
                'subtitle' => 'Essai d\'ontologie phénoménologique',
                'abstract' => 'L\'œuvre majeure de Jean-Paul Sartre sur l\'existentialisme, qui explore la liberté, la conscience et l\'être pour-soi.',
                'isbn' => '9782070401000',
                'publication_year' => 1943,
                'language' => 'fr',
                'document_type' => 'livre',
                'pages' => 750,
                'status' => 'published',
            ],
            [
                'title' => 'Sur l\'Origine des Espèces',
                'subtitle' => 'Par la sélection naturelle',
                'abstract' => 'L\'ouvrage révolutionnaire de Charles Darwin qui présente sa théorie de l\'évolution par sélection naturelle.',
                'isbn' => '9782070403000',
                'publication_year' => 1859,
                'language' => 'fr',
                'document_type' => 'livre',
                'pages' => 600,
                'status' => 'published',
            ],
        ];

        // Crée chaque référence et associe les éléments
        foreach ($references as $index => $refData) {
            // Ajoute les IDs des relations
            $refData['category_id'] = $categories->random()->id;
            $refData['publisher_id'] = $publishers->random()->id;
            $refData['uploaded_by'] = $users->random()->id;
            $refData['download_count'] = rand(0, 500);
            $refData['view_count'] = rand(0, 2000);

            // Crée la référence
            $reference = Reference::create($refData);

            // Associe 1 à 3 auteurs aléatoires à la référence
            $reference->authors()->attach(
                $authors->random(rand(1, 3))->pluck('id')->toArray()
            );

            // Ajoute 2 à 5 mots-clés aléatoires pour la référence
            $keywords = ['roman', 'classique', 'sciences', 'philosophie', 'féminisme', 'évolution', 'relativité', 'littérature', 'histoire', 'physique'];
            $selectedKeywords = array_rand(array_flip($keywords), rand(2, 5));
            foreach ((array)$selectedKeywords as $keyword) {
                DB::table('reference_keywords')->insert([
                    'reference_id' => $reference->id,
                    'keyword' => $keyword,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Ajoute 15 références aléatoires supplémentaires avec le factory
        $referencesAleatoires = Reference::factory(15)->create();
        foreach ($referencesAleatoires as $ref) {
            // Associe des auteurs et mots-clés aux références aléatoires
            $ref->authors()->attach($authors->random(rand(1, 2))->pluck('id')->toArray());
            
            $keywordsAleatoires = ['informatique', 'droit', 'médecine', 'sciences', 'littérature', 'économie', 'art', 'sociologie', 'mathématiques', 'chimie'];
            $selectedKeywordsAleatoires = array_rand(array_flip($keywordsAleatoires), rand(2, 4));
            foreach ((array)$selectedKeywordsAleatoires as $keyword) {
                DB::table('reference_keywords')->insert([
                    'reference_id' => $ref->id,
                    'keyword' => $keyword,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
