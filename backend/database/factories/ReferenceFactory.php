<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Reference>
 */
class ReferenceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'subtitle' => fake()->sentence(6),
            'abstract' => fake()->paragraph(3),
            'isbn' => fake()->isbn13(),
            'publication_year' => fake()->year(),
            'language' => fake()->randomElement(['fr', 'en', 'autre']),
            'document_type' => fake()->randomElement(['livre', 'memoire', 'these', 'article', 'revue', 'rapport', 'guide', 'autre']),
            'category_id' => \App\Models\Categorie::inRandomOrder()->value('id'),
            'publisher_id' => \App\Models\Publisher::inRandomOrder()->value('id'),
            'uploaded_by' => \App\Models\User::inRandomOrder()->value('id'),
            
            'cover_image' => 'https://picsum.photos/400/600?random=' . fake()->randomNumber(5),
            'file_path' => fake()->filePath(),
            'pages' => fake()->numberBetween(50, 500),
            'download_count' => fake()->numberBetween(0, 1000),
            'view_count' => fake()->numberBetween(0, 5000),
            'status' => fake()->randomElement(['draft', 'published', 'archived']),
        ];
    }
}
