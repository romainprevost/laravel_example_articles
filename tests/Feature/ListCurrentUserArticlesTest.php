<?php

namespace Tests\Feature;

use App\Http\Controllers\CurrentUserArticlesController;
use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ListCurrentUserArticlesTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_guest_cannot_access_my_articles_page()
    {
        $this
            ->getJson(action(CurrentUserArticlesController::class))
            ->assertUnauthorized();
    }

    public function test_a_user_can_see_its_latest_ten_own_articles_with_their_tags()
    {
        $perPage = 10;
        $otherArticles = Article::factory()->create();

        $user = User::factory()->create();
        $articles = Article::factory()->for($user)
            ->count(15)
            ->create()
            ->sortByDesc('created_at')
            ->take($perPage);

        $response = $this->actingAs($user)->get(action(CurrentUserArticlesController::class))->assertOk();

//        $this->assertEquals(1, $response->json('current_page'));
        // Avec la pagination, Laravel met les modèles dans une clé data de la réponse, les autres clés correspondent
        // à la pagination : page actuelle, nombre par page, nombre total de modèles etc...
        $this->assertCount($perPage, $response->json('data'));

        $returnedArticles = $response->json('data');

        $this->assertEquals($articles->pluck('id'), collect($returnedArticles)->pluck('id'));

        collect($returnedArticles)->each(function(array $article) {
            $this->assertArrayHasKey('tags', $article);
        });
    }

    public function test_a_user_also_sees_its_unpublished_articles()
    {
        $user = User::factory()->create();
        $published = Article::factory()->for($user)->create();
        $unPublished = Article::factory()->for($user)->unpublished()->create();

        $response = $this->actingAs($user)->get(action(CurrentUserArticlesController::class));

        $returnedArticlesIds = collect($response->json('data'))->pluck('id');

        $this->assertContains($published->id, $returnedArticlesIds);
        $this->assertContains($unPublished->id, $returnedArticlesIds);
    }
}
