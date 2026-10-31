<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Row-level publication for the content types that had no flag (greenruby-web docs/i18n-audit/12 §2.2, phase 1C-2).
 * Default 'published', so every existing row stays exactly as visible as today (14 §4.3). Appended columns:
 * INSTANT on MySQL ≥ 8.0.29. Article (is_published + publish_date), destination (status), page (is_visible) and
 * service (status) keep their own flags.
 */
return new class extends Migration
{
    public const TABLES = ['app_cruise', 'app_cabin', 'app_itinerary', 'app_exp_activity', 'app_faq', 'app_group', 'app_category'];

    public function up(): void
    {
        foreach (self::TABLES as $table) {
            if (!Schema::hasColumn($table, 'publish_state')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->enum('publish_state', ['published', 'unpublished'])->default('published');
                });
            }
        }
    }

    public function down(): void
    {
        foreach (self::TABLES as $table) {
            if (Schema::hasColumn($table, 'publish_state')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->dropColumn('publish_state');
                });
            }
        }
    }
};
