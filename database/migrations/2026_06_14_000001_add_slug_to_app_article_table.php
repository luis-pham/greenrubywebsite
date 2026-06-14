<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\BackEnd\Helpers\Utilities;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('app_article', function (Blueprint $table) {
            $table->string('slug', 255)->nullable()->after('title');
        });

        $articles = DB::table('app_article')
            ->select('id', 'language_id', 'category_id', 'title', 'slug')
            ->orderBy('id')
            ->get();

        $used = [];
        foreach ($articles as $article) {
            $baseSlug = Utilities::convertToAlias($article->title);
            if ($baseSlug === '') {
                $baseSlug = 'article-' . $article->id;
            }

            $key = $article->language_id . ':' . ($article->category_id ?? '0');
            if (!isset($used[$key])) {
                $used[$key] = [];
            }

            $slug = $baseSlug;
            $counter = 2;
            while (in_array($slug, $used[$key], true)) {
                $slug = $baseSlug . '-' . $counter;
                $counter++;
            }

            $used[$key][] = $slug;

            DB::table('app_article')
                ->where('id', $article->id)
                ->update(['slug' => $slug]);
        }

        Schema::table('app_article', function (Blueprint $table) {
            $table->unique(['language_id', 'category_id', 'slug'], 'app_article_lang_cat_slug_unique');
        });
    }

    public function down(): void
    {
        Schema::table('app_article', function (Blueprint $table) {
            $table->dropUnique('app_article_lang_cat_slug_unique');
            $table->dropColumn('slug');
        });
    }
};
