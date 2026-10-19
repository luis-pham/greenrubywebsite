<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Writing app (write.greenrubycruises.com): login tokens, the content plan and a focus keyword per article.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('editor_tokens', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->index();
            $table->char('token_hash', 64)->unique();
            $table->dateTime('expires_at');
            $table->dateTime('last_used_at')->nullable();
            $table->dateTime('created_at');
        });

        // One row per planned article (imported from the pillar/cluster content plan).
        Schema::create('app_article_plan', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code', 16)->unique();
            $table->string('pillar', 8);
            $table->string('pillar_name', 120)->nullable();
            $table->string('kind', 10)->default('cluster'); // pillar | cluster
            $table->string('lang', 2)->default('en');
            $table->string('category_slug', 120)->nullable();
            $table->string('title', 255);
            $table->string('primary_kw', 255)->nullable();
            $table->text('secondary_kw')->nullable();
            $table->char('intent', 1)->nullable();
            $table->unsignedInteger('words')->nullable();
            $table->unsignedTinyInteger('priority')->default(2);
            $table->string('target_month', 40)->nullable();
            $table->json('money_links')->nullable();
            $table->json('siblings')->nullable();
            $table->text('note')->nullable();
            $table->string('status', 10)->default('idea'); // idea | wait | skip (writing/published come from the article)
            $table->unsignedInteger('article_id')->nullable()->index();
            $table->timestamps();
        });

        Schema::table('app_article', function (Blueprint $table) {
            $table->string('focus_keyword', 255)->nullable()->after('seo_description');
        });
    }

    public function down(): void
    {
        Schema::table('app_article', function (Blueprint $table) {
            $table->dropColumn('focus_keyword');
        });
        Schema::dropIfExists('app_article_plan');
        Schema::dropIfExists('editor_tokens');
    }
};
