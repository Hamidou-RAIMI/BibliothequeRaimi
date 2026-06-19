<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

    // compte de l'utilisateur 
   

    $this->call([
        UserSeeder::class,
        ReferenceSeeder::class,
        AuthorSeeder::class,
        PublisherSeeder::class,
        CategorieSeeder::class,
    ]);
    }
}
