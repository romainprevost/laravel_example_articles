<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ArticleController extends Controller
{
    public function index()
    {
        // with permet de chercher tous les auteurs des articles en même temps que l'on les récupère
        // Eloquent ne fera que 2 requêtes SQL

        // On retourne les articles avec ou sans pagination
        return Article::with('user', 'tags')
            ->latest('published_at')
            ->published() // Il s'agit d'un query scope qui permet d'extraire une contrainte sur la requête SQL directement dans le modèle
            ->paginate(10)
        ;

//        Identique à:
//        return Article::with('user', 'tags')
//            ->where('published_at', '<=', now())
//            ->orderByDesc('published_at') // ou ->orderByDesc('published_at')
//            ->take(10)
//            ->skip(request()->has('page') ? (request()->input('page') - 1) * 10 : 0)
//            ->get();

        // La pagination permet de gérer automatiquement le changement de page via un paramètre page dans l'objet Request
        // si il est présent Laravel l'utilisera pour fixer l'offset de la requête
    }

    public function show(Article $article)
    {
        // Ici, on récupère l'auteur de l'article pour qu'il soit disponible dans la vue
        return $article->load('user');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'        => ['bail', 'required', 'string'],
            'content'      => ['bail', 'required', 'string'],
            'published_at' => ['date'],
            'tags.*'         => ['exists:' . Tag::class . ',id'], // the tag ids should exist in tags table
        ]);


        $article = new Article();
        $article->title = $request->input('title');
        $article->content = $request->input('content');
        $article->published_at = $request->input('published_at', now());

        // On peut associer l'article que l'on crée à l'utilisateur directement en utilisant la relation
        // comme ça il n'est pas nécessaire de savoir le nom de la colonne sur laquelle la relation se fait
        $article->user()->associate(auth()->user());
        // C'est identique à
//        $article->user_id = auth()->user()->id;

        $article->save();

        $article->tags()->attach($request->tags);

        return new JsonResponse($article, Response::HTTP_CREATED);
    }

    public function update(Article $article, Request $request)
    {
        // Autorisation
        $this->authorizeUser($article);

        $request->validate([
            'title'        => ['bail', 'required', 'string'],
            'content'      => ['bail', 'required', 'string'],
            'published_at' => ['sometimes', 'date'],
        ]);

        $article->title = $request->input('title');
        $article->content = $request->input('content');
        $article->published_at = $request->input('published_at', null);

        $article->save();

        return new JsonResponse($article);
    }

    public function destroy(Article $article)
    {
        $this->authorizeUser($article);

        $article->delete();
    }

    protected function authorizeUser(Article $article)
    {
        $user = auth()->user();
        // On peut bien sûr tester directement l'attribut user_id de $article et le comparer à $user->id
        // C'est ce que font les méthodes is() et isNot() en plus d'autres vérifications
        if ($article->user()->isNot($user) && !$user->is_admin)
        {
            abort(401);
        }
    }

}
