<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Article;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        // Création d'un administrateur
        User::factory()->admin()->create([
            'name' => 'Administrateur',
            'email' => 'admin@example.com',
        ]);

        // Création de 50 articles, chacun avec 3 tags
        Article::factory()
            ->count(50)
            ->has(
                Tag::factory()->count(3)
            )->create();
    }
}
