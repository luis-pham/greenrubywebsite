<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\BackEnd\Helpers\Utilities;

/**
 * Stored slugs (12 §2.3, 14 §4.2, owner decision #4 of 2026-11; phase 1C-3).
 *
 * - `slug` on cruise, cabin, itinerary and experience, backfilled with exactly today's derivation
 *   (Utilities::convertToAlias(name)), so every existing URL stays byte-identical; readers fall back to that
 *   derivation while a row has no slug (App\Services\Content\Slugs).
 * - Cruise SEO title/description columns (12 §2.3; empty, the Content API keeps its current fallbacks).
 * - `app_slug_history`: every deliberate slug change, used to redirect old public URLs. No uniqueness index on
 *   app_cabin slugs: 28 legitimate duplicates per language (A-5); URLs carry the id.
 */
return new class extends Migration
{
    private const TABLES = ['app_cruise', 'app_cabin', 'app_itinerary', 'app_exp_activity'];

    public function up(): void
    {
        foreach (self::TABLES as $table) {
            if (!Schema::hasColumn($table, 'slug')) {
                Schema::table($table, fn (Blueprint $t) => $t->string('slug', 160)->nullable());
            }
        }
        if (!Schema::hasColumn('app_cruise', 'seo_title')) {
            Schema::table('app_cruise', function (Blueprint $t) {
                $t->string('seo_title', 65)->nullable();
                $t->string('seo_description', 255)->nullable();
            });
        }
        Schema::create('app_slug_history', function (Blueprint $t) {
            $t->bigIncrements('id');
            $t->string('entity_type', 32);
            $t->unsignedInteger('entity_id');
            $t->unsignedInteger('language_id');
            $t->string('old_slug', 160);
            $t->string('new_slug', 160);
            $t->unsignedInteger('actor')->nullable();
            $t->string('reason', 255)->nullable();
            $t->dateTime('created_at');
            $t->index(['entity_type', 'language_id', 'old_slug']);
        });

        // Backfill (14 §4.2): idempotent, chunked, exactly today's derived slug, for en/vi rows only (other
        // languages get an editor-chosen slug, never a derived one).
        $legacy = DB::table('ad_language')->whereIn('code', ['en', 'vi'])->pluck('id')->all();
        foreach (self::TABLES as $table) {
            DB::table($table)->whereNull('slug')->whereIn('language_id', $legacy)->orderBy('id')->chunkById(200, function ($rows) use ($table) {
                foreach ($rows as $row) {
                    $slug = Utilities::convertToAlias((string) $row->name);
                    DB::table($table)->where('id', $row->id)->update(['slug' => $slug === '' ? null : $slug]);
                }
            });
        }
    }

    public function down(): void
    {
        // Only safe before slugs diverge from names (a renamed row would get its derived URL back) — 14 §6.
        Schema::dropIfExists('app_slug_history');
        if (Schema::hasColumn('app_cruise', 'seo_title')) {
            Schema::table('app_cruise', fn (Blueprint $t) => $t->dropColumn(['seo_title', 'seo_description']));
        }
        foreach (self::TABLES as $table) {
            if (Schema::hasColumn($table, 'slug')) {
                Schema::table($table, fn (Blueprint $t) => $t->dropColumn('slug'));
            }
        }
    }
};
