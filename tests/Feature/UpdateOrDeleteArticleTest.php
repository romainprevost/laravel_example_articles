<?php

namespace Tests\Feature;

use App\Http\Controllers\ArticleController;
use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class UpdateOrDeleteArticleTest extends TestCase
{
    use DatabaseTransactions;
    public function test_guest_cannot_update_an_article()
    {
        $article = Article::factory()->create();
        $newData = Article::factory()->make();
        $this
            ->putJson(action([ArticleController::class, 'update'], $article), $newData->toArray())
            ->assertUnauthorized();
        // On peut utiliser la méthode put mais dans ce cas Laravel redirige par défaut vers la page de login
        // C'est donc cette redirection qu'il faudrait tester et non pas le code de la réponse
    }

    public function test_user_can_update_an_article()
    {
        $user = User::factory()->create();
        $article = Article::factory()
            ->for($user)
            ->create();

        $newData = Article::factory()->unpublished()->make();

        $this
            ->actingAs($user)
            ->put(action([ArticleController::class, 'update'], $article), $newData->toArray())
            ->assertOk();

        $article->refresh();

        $this->assertEquals($newData->title, $article->title);
        $this->assertEquals($newData->content, $article->content);
        $this->assertEquals($newData->published_at, $article->published_at);
    }

    public function test_admin_can_update_an_article()
    {
        $user = User::factory()->admin()->create();
        $article = Article::factory()->create();

        $newData = Article::factory()->unpublished()->make();

        $this
            ->actingAs($user)
            ->put(action([ArticleController::class, 'update'], $article), $newData->toArray())
            ->assertOk();

        $article->refresh();

        $this->assertEquals($newData->title, $article->title);
        $this->assertEquals($newData->content, $article->content);
        $this->assertEquals($newData->published_at, $article->published_at);
    }

    public function test_title_is_mandatory()
    {
        $article= Article::factory()->create();
        $newData = Article::factory()->make(['title' => null]);
        $this
            ->actingAs($article->user)
            ->putJson(action([ArticleController::class, 'update'], $article), $newData->toArray())
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        // On peut utiliser la méthode put mais dans ce cas Laravel redirige par défaut vers la page de login
        // C'est donc cette redirection qu'il faudrait tester et non pas le code de la réponse
    }

    public function test_content_is_mandatory()
    {
        $article= Article::factory()->create();
        $newData = Article::factory()->make(['content' => null]);
        $this
            ->actingAs($article->user)
            ->putJson(action([ArticleController::class, 'update'], $article), $newData->toArray())
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        // On peut utiliser la méthode put mais dans ce cas Laravel redirige par défaut vers la page de login
        // C'est donc cette redirection qu'il faudrait tester et non pas le code de la réponse
    }

    public function test_guest_cannot_delete_an_article()
    {
        $article = Article::factory()->create();

        $this
            ->deleteJson(action([ArticleController::class, 'destroy'], $article))
            ->assertUnauthorized();
        // On peut utiliser la méthode delete mais dans ce cas Laravel redirige par défaut vers la page de login
        // C'est donc cette redirection qu'il faudrait tester et non pas le code de la réponse
    }

    public function test_user_cannot_delete_an_article_it_does_not_owned()
    {
        $article = Article::factory()->create();
        $user = User::factory()->create();
        $this
            ->actingAs($user)
            ->deleteJson(action([ArticleController::class, 'destroy'], $article))
            ->assertUnauthorized();
        // On peut utiliser la méthode delete mais dans ce cas Laravel redirige par défaut vers la page de login
        // C'est donc cette redirection qu'il faudrait tester et non pas le code de la réponse
    }

    public function test_user_can_delete_an_article()
    {
        $article = Article::factory()->create();

        $this
            ->actingAs($article->user)
            ->deleteJson(action([ArticleController::class, 'destroy'], $article))
            ->assertOk();

        $this->assertEquals(0, Article::count());
        // On peut utiliser la méthode delete mais dans ce cas Laravel redirige par défaut vers la page de login
        // C'est donc cette redirection qu'il faudrait tester et non pas le code de la réponse
    }

    public function test_admin_can_delete_an_article()
    {
        $article = Article::factory()->create();

        $this
            ->actingAs(User::factory()->admin()->create())
            ->deleteJson(action([ArticleController::class, 'destroy'], $article))
            ->assertOk();

        $this->assertEquals(0, Article::count());
    }
}
