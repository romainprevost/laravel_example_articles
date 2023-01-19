<?php

namespace Tests\Feature;

use App\Http\Controllers\ArticleController;
use App\Models\Article;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class CreateArticleTest extends TestCase
{
    use DatabaseTransactions;

    public function test_guest_cannot_create_an_article()
    {
        $article = Article::factory()->make();
        $this
            ->postJson(action([ArticleController::class, 'store']), $article->toArray())
            ->assertUnauthorized();

        // On peut utiliser la méthode post mais dans ce cas Laravel redirige par défaut vers la page de login
        // C'est donc cette redirection qu'il faudrait tester et non pas le code de la réponse
    }

    public function test_user_can_create_an_article()
    {
        $article = Article::factory()->make();
        $user = User::factory()->create();
        $this
            ->actingAs($user)
            ->post(action([ArticleController::class, 'store']), $article->toArray())
            ->assertCreated();

        $articles = $user->articles()->get();

        $this->assertCount(1, $articles);
        $this->assertEquals($article->title, $articles[0]->title);
        $this->assertEquals($article->content, $articles[0]->content);
        $this->assertNull($articles[0]->published_at);
    }
    public function test_user_can_create_an_article_to_be_published()
    {
        $article = Article::factory()->unpublished()->make();
        $user = User::factory()->create();

        $this
            ->actingAs($user)
            ->post(action([ArticleController::class, 'store']), $article->toArray())
            ->assertCreated();

        $this->assertNotNull($user->articles()->first()->published_at);
    }

    public function test_title_is_mandatory()
    {
        $article = Article::factory()->make(['title' => null]);
        $user = User::factory()->create();
        $this
            ->actingAs($user)
            ->postJson(action([ArticleController::class, 'store']), $article->toArray())
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        // On peut utiliser la méthode post mais dans ce cas Laravel redirige par défaut vers la page de login
        // C'est donc cette redirection qu'il faudrait tester et non pas le code de la réponse

        $this->assertCount(0, Article::all());
    }

    public function test_content_is_mandatory()
    {
        $article = Article::factory()->make(['content' => null]);
        $user = User::factory()->create();
        $this
            ->actingAs($user)
            ->postJson(action([ArticleController::class, 'store']), $article->toArray())
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        // On peut utiliser la méthode post mais dans ce cas Laravel redirige par défaut vers la page de login
        // C'est donc cette redirection qu'il faudrait tester et non pas le code de la réponse

        $this->assertCount(0, Article::all());
    }

    public function test_a_user_can_add_tags_during_creation_of_an_article()
    {
        $this->withoutExceptionHandling();
        $article = Article::factory()->make();
        $tags = Tag::factory()->count(3)->create();
        $this
            ->actingAs($article->user)
            ->post(action([ArticleController::class, 'store']), [
                ...$article->toArray(),
                'tags' => $tags->pluck('id')
            ]);

        $createdArticle = Article::with('tags')->first();

        $this->assertCount(3, $createdArticle->tags);
    }
}
