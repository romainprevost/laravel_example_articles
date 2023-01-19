<?php

use App\Models\Article;
use App\Models\Tag;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('article_tag', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Tag::class)->constrained()->onDelete('cascade');
            $table->foreignIdFor(Article::class)->constrained()->onDelete('cascade');
            // C'est équivalent à:
            // $table->foreignId('tag_id');
            // $table->foreignId('article_id');
            $table->timestamps();

            // On précise que le couple article_id et tag_id est unique pour ne pas pouvoir ajouter
            // plusieurs fois le même tag à un article, la protection est au niveau de la BDD
            $table->unique(['article_id', 'tag_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('article_tag');
    }
};
