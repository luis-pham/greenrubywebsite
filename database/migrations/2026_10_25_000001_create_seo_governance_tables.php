<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * SEO content governance (docs/seo-audit/14):
 *   app_seo_owner      which URL owns a primary keyword + search intent, per language (G1)
 *   app_seo_guard_log  every cannibalization check at AI queue / plan start / publish, and every override (G3)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_seo_owner', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('language_id');
            $table->string('keyword', 191);
            // KeywordMatcher::normalize(keyword): the uniqueness key, so "Halong" and "Ha Long" are one row.
            $table->string('keyword_key', 191);
            // I informational, C commercial, T transactional, N navigational (same letters as app_article_plan).
            $table->char('intent', 1)->nullable();
            // Relative URL of the owner; null while the intent has no page yet (status "gap").
            $table->string('owner_url', 255)->nullable();
            $table->string('owner_type', 20)->nullable(); // page, hub, cruise, itinerary, cabin, experience, article, destination, guide
            $table->unsignedInteger('owner_ref')->nullable(); // record id when owner_type is article / destination
            $table->string('page_role', 10)->nullable(); // hub, pillar, detail
            $table->string('cluster', 120)->nullable();
            $table->string('pillar', 120)->nullable();
            $table->json('supporting_keywords')->nullable();
            $table->string('status', 10)->default('active'); // active, gap, retired
            $table->text('note')->nullable();
            $table->timestamps();
            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('updated_by')->nullable();
            $table->unique(['language_id', 'keyword_key', 'intent'], 'app_seo_owner_unique');
            $table->index(['language_id', 'status']);
        });

        Schema::create('app_seo_guard_log', function (Blueprint $table) {
            $table->increments('id');
            $table->string('action', 20); // ai_queue, plan_start, publish, check
            $table->string('entity_type', 10); // plan, article
            $table->unsignedInteger('entity_id');
            $table->unsignedInteger('language_id')->nullable();
            $table->string('keyword', 255)->nullable();
            $table->string('verdict', 20); // clear, healthy_overlap, conflict
            $table->string('recommendation', 20); // create_new, update_existing, merge
            $table->string('outcome', 20); // allowed, blocked, overridden
            $table->text('override_reason')->nullable();
            $table->json('report');
            $table->unsignedInteger('user_id')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->index(['entity_type', 'entity_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_seo_guard_log');
        Schema::dropIfExists('app_seo_owner');
    }
};
