<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Editorial workflow per translated row (12 §2.4) and the audit log of every translation-link change (12 §2.6),
 * phase 1C-2. No rows for legacy EN/VI content: a missing translation_meta row means "legacy, edited directly".
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('translation_meta', function (Blueprint $t) {
            $t->string('entity_type', 32);
            $t->unsignedInteger('entity_id');
            $t->unsignedInteger('language_id');
            $t->enum('workflow', ['draft', 'translation_review', 'seo_review', 'legal_review', 'approved'])->default('draft');
            $t->unsignedInteger('source_entity_id')->nullable();
            $t->unsignedInteger('source_language_id')->nullable();
            $t->char('source_hash', 64)->nullable();
            $t->dateTime('source_hash_at')->nullable();
            $t->unsignedInteger('translated_by')->nullable();
            $t->unsignedInteger('reviewed_by')->nullable();
            $t->dateTime('reviewed_at')->nullable();
            $t->unsignedInteger('seo_reviewed_by')->nullable();
            $t->dateTime('seo_reviewed_at')->nullable();
            $t->unsignedInteger('legal_approved_by')->nullable();
            $t->dateTime('legal_approved_at')->nullable();
            $t->dateTime('updated_at');
            $t->primary(['entity_type', 'entity_id']);
        });

        Schema::create('content_translation_log', function (Blueprint $t) {
            $t->bigIncrements('id');
            $t->enum('action', ['attach', 'detach', 'regroup', 'freeze', 'restore']);
            $t->string('entity_type', 32);
            $t->unsignedInteger('entity_id');
            $t->unsignedInteger('language_id');
            $t->char('old_group_key', 36)->nullable();
            $t->char('new_group_key', 36)->nullable();
            $t->unsignedInteger('actor')->nullable();
            $t->string('reason', 255)->nullable();
            $t->dateTime('created_at');
            $t->index(['entity_type', 'entity_id']);
        });
    }

    public function down(): void
    {
        // Only before phase 2: afterwards these tables hold editorial history (14 §6).
        if (DB::table('translation_meta')->exists()) {
            throw new RuntimeException('Refusing to drop translation_meta: it has rows (editorial history).');
        }
        Schema::dropIfExists('content_translation_log');
        Schema::dropIfExists('translation_meta');
    }
};
