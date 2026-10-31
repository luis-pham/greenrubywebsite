<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 2C: row-level publication for amenities (app_amenity had no flag, so a new language's amenities would be
 * visible as soon as the language went public). Same column as 2026_11_02_000001: default 'published', so every
 * existing en/vi row stays exactly as visible as today (public reads filter it for new languages only:
 * IntlVisibility). Rows that already exist in a new (intl) language are set 'unpublished': they were never reviewed.
 * Additive, appended (INSTANT on MySQL >= 8.0.29), reversible: down() drops the column (en/vi never read it).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('app_amenity', 'publish_state')) {
            Schema::table('app_amenity', function (Blueprint $t) {
                $t->enum('publish_state', ['published', 'unpublished'])->default('published');
            });
        }
        $intl = Schema::hasColumn('ad_language', 'route_style')
            ? DB::table('ad_language')->where('route_style', 'intl')->pluck('id')
            : DB::table('ad_language')->whereNotIn('code', ['en', 'vi'])->pluck('id');
        if ($intl->isNotEmpty()) {
            DB::table('app_amenity')->whereIn('language_id', $intl->all())->update(['publish_state' => 'unpublished']);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('app_amenity', 'publish_state')) {
            Schema::table('app_amenity', function (Blueprint $t) {
                $t->dropColumn('publish_state');
            });
        }
    }
};
