<?php

namespace Tests\Feature;

use App\Http\Controllers\ArticleController;
use App\Models\Article;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ListArticlesTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_everyone_can_see_the_latest_ten_articles_with_their_tags()
    {
        $perPage = 10;

        // On crée 20 articles avec des dates de publiclation différentes qui seront dans le passé (il y a 1 jour)
        /** @var User $user */
        $articles = Article::factory()
            ->count(20)
            ->state(new Sequence(
                fn ($sequence) => ['published_at' => now()->subDay()->addSeconds($sequence->index)],
            ))
            ->create()
            ->sortByDesc('published_at')
            ->take($perPage);

        $response = $this->get(action([ArticleController::class, 'index']))->assertOk();

        // Avec la pagination, Laravel met les modèles dans une clé data de la réponse, les autres clés correspondent
        // à la pagination: page actuelle, nombre par page, nombre total de modèles etc..
        $this->assertCount($perPage, $response->json('data'));
//        $this->assertEquals(1, $response->json('current_page'));

        $returnedArticles = $response->json('data');

        $this->assertEquals($articles->pluck('id'), collect($returnedArticles)->pluck('id'));

        collect($returnedArticles)->each(function(array $article) {
            $this->assertArrayHasKey('tags', $article);
        });
    }

    public function test_only_published_articles_are_returned()
    {
        $article = Article::factory()->create();
        $unpublishedArticles = Article::factory()->unpublished()->count(5)->create();

        $response = $this->get(action([ArticleController::class, 'index']))->assertOk();
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals($article->id, $response->json('data')[0]['id']);
    }

    public function test_list_articles_uses_pagination()
    {
        $perPage = 10;

        // On crée 20 articles avec des dates de publiclation différentes qui seront dans le passé (il y a 1 jour)
        /** @var User $user */
        $articles = Article::factory()
            ->count(20)
            ->state(new Sequence(
                fn ($sequence) => ['published_at' => now()->subDay()->addSeconds($sequence->index)],
            ))
            ->create()
            ->sortByDesc('published_at')
            ->skip($perPage)
            ->take($perPage);

        $response = $this->get(action([ArticleController::class, 'index'], ['page' => 2]))->assertOk();

        $returnedArticles = $response->json('data');

        $this->assertEquals($articles->pluck('id'), collect($returnedArticles)->pluck('id'));
    }
}
