<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * AI drafting (articles:ai-draft-run): a queue state per plan item, and on the article the drafting
 * metadata (sources, items to confirm, model, usage) shown to the editor in the writing app.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('app_article_plan', function (Blueprint $table) {
            $table->string('ai_status', 12)->nullable()->after('evidence'); // queued | running | done | error
            $table->text('ai_error')->nullable()->after('ai_status');
            $table->dateTime('ai_queued_at')->nullable()->after('ai_error');
            $table->dateTime('ai_finished_at')->nullable()->after('ai_queued_at');
            $table->index('ai_status');
        });
        Schema::table('app_article', function (Blueprint $table) {
            $table->json('ai_meta')->nullable()->after('evidence_status');
        });
    }

    public function down(): void
    {
        Schema::table('app_article_plan', function (Blueprint $table) {
            $table->dropIndex(['ai_status']);
            $table->dropColumn(['ai_status', 'ai_error', 'ai_queued_at', 'ai_finished_at']);
        });
        Schema::table('app_article', fn (Blueprint $table) => $table->dropColumn('ai_meta'));
    }
};
