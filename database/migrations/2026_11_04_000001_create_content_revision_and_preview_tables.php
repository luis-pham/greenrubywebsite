<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 1E-d (all additive, new tables only):
 *   content_revision        pending edits of PUBLISHED rows in workflow-controlled languages (12 §2.5): the live row
 *                           changes only when a revision is approved, in one transaction.
 *   content_preview_token   single-use preview tokens (12 §8, A-7): only a hash is stored; expires in minutes.
 *   content_preview_session the short preview session a verified token opens (scoped to one entity and language).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_revision', function (Blueprint $t) {
            $t->bigIncrements('id');
            $t->string('entity_type', 32);
            $t->unsignedInteger('entity_id');
            $t->json('fields');
            $t->enum('workflow', ['draft', 'translation_review', 'seo_review', 'legal_review', 'approved'])->default('draft');
            $t->unsignedInteger('created_by')->nullable();
            $t->unsignedInteger('updated_by')->nullable(); // last editor: one open revision belongs to one editor
            $t->dateTime('created_at');
            $t->dateTime('updated_at')->nullable();
            $t->dateTime('applied_at')->nullable();
            $t->unsignedInteger('applied_by')->nullable();
            $t->dateTime('discarded_at')->nullable(); // rejected and dropped; the public version never changed
            $t->unsignedInteger('discarded_by')->nullable();
            $t->index(['entity_type', 'entity_id', 'applied_at']);
        });
        Schema::create('content_preview_token', function (Blueprint $t) {
            $t->bigIncrements('id');
            $t->char('token_hash', 64)->unique();
            $t->unsignedInteger('user_id');
            $t->string('entity_type', 32);
            $t->unsignedInteger('entity_id');
            $t->unsignedInteger('language_id');
            $t->unsignedBigInteger('revision_id')->nullable();
            $t->dateTime('expires_at');
            $t->dateTime('used_at')->nullable();
            $t->dateTime('created_at');
        });
        Schema::create('content_preview_session', function (Blueprint $t) {
            $t->bigIncrements('id');
            $t->char('session_hash', 64)->unique();
            $t->unsignedBigInteger('token_id');
            $t->unsignedInteger('user_id');
            $t->string('entity_type', 32);
            $t->unsignedInteger('entity_id');
            $t->unsignedInteger('language_id');
            $t->unsignedBigInteger('revision_id')->nullable();
            $t->dateTime('expires_at');
            $t->dateTime('revoked_at')->nullable();
            $t->dateTime('created_at');
        });
    }

    public function down(): void
    {
        // Pending revisions are editorial history (14 §6): refuse to drop them.
        if (Schema::hasTable('content_revision') && DB::table('content_revision')->exists()) {
            throw new RuntimeException('Refusing to drop content_revision: it has rows (editorial history).');
        }
        Schema::dropIfExists('content_preview_session');
        Schema::dropIfExists('content_preview_token');
        Schema::dropIfExists('content_revision');
    }
};
