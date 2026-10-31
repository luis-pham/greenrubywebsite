<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Phase 1E-e (12 §2.8, plan M9): app_article_plan.lang holds a BCP 47 tag (en, vi, ja, zh-Hant), not two letters.
 * varchar(2) → varchar(35): MySQL 8 widens a VARCHAR within the same length-byte size in place (no table copy).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement('SET SESSION lock_wait_timeout = 5');
            DB::statement("ALTER TABLE app_article_plan MODIFY lang VARCHAR(35) NOT NULL DEFAULT 'en', ALGORITHM=INPLACE, LOCK=NONE");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }
        if (DB::table('app_article_plan')->whereRaw('CHAR_LENGTH(lang) > 2')->exists()) {
            throw new RuntimeException('Refusing to narrow app_article_plan.lang: rows use longer language tags.');
        }
        DB::statement('SET SESSION lock_wait_timeout = 5');
        DB::statement("ALTER TABLE app_article_plan MODIFY lang VARCHAR(2) NOT NULL DEFAULT 'en'");
    }
};
