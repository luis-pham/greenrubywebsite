<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Locale registry (greenruby-web docs/i18n-audit/12 §2.1, phase 1B). Additive: every column is appended (INSTANT on
 * MySQL ≥ 8.0.29), and en/vi are backfilled to exactly what the site does today: public, legacy routes, EN without
 * a URL prefix, VI under /vi, each its own price list, x-default EN then VI, edited directly (no workflow).
 * A row of any other language gets the column defaults: public status would still need a frontend that supports it
 * (App\Services\Locale\LocaleRegistry::isEffectivePublic).
 */
return new class extends Migration
{
    private const COLUMNS = [
        'site_status', 'native_name', 'english_name', 'url_prefix', 'route_style', 'hreflang', 'og_locale', 'intl_tag',
        'direction', 'price_list_language_id', 'ui_fallback_language_id', 'xdefault_priority', 'workflow_controlled',
    ];

    private const LEGACY = [
        'en' => ['native_name' => 'English', 'english_name' => 'English', 'url_prefix' => '', 'hreflang' => 'en', 'og_locale' => 'en_US', 'intl_tag' => 'en-US', 'xdefault_priority' => 1],
        'vi' => ['native_name' => 'Tiếng Việt', 'english_name' => 'Vietnamese', 'url_prefix' => 'vi', 'hreflang' => 'vi', 'og_locale' => 'vi_VN', 'intl_tag' => 'vi-VN', 'xdefault_priority' => 2],
    ];

    public function up(): void
    {
        Schema::table('ad_language', function (Blueprint $table) {
            // Default 'disabled' for rows added later; en/vi are set to 'public' below.
            $table->enum('site_status', ['disabled', 'editing', 'public'])->default('disabled');
            $table->string('native_name', 64)->nullable();
            $table->string('english_name', 64)->nullable();
            $table->string('url_prefix', 32)->nullable();
            $table->enum('route_style', ['legacy', 'intl'])->default('intl');
            $table->string('hreflang', 35)->nullable();
            $table->string('og_locale', 35)->nullable();
            $table->string('intl_tag', 35)->nullable();
            $table->enum('direction', ['ltr', 'rtl'])->default('ltr');
            $table->unsignedInteger('price_list_language_id')->nullable();
            $table->unsignedInteger('ui_fallback_language_id')->nullable();
            $table->unsignedSmallInteger('xdefault_priority')->nullable();
            $table->boolean('workflow_controlled')->default(true);
        });

        foreach (self::LEGACY as $code => $values) {
            $id = DB::table('ad_language')->where('code', $code)->value('id');
            if ($id === null) {
                continue;
            }
            DB::table('ad_language')->where('id', $id)->update($values + [
                'site_status' => 'public',
                'route_style' => 'legacy',
                'direction' => 'ltr',
                'price_list_language_id' => $id,
                'workflow_controlled' => false,
            ]);
        }
        $this->flushRegistryCache();
    }

    public function down(): void
    {
        // 14 §6: never drop the registry while another language exists; its status would be lost.
        $others = DB::table('ad_language')->whereNotIn('code', array_keys(self::LEGACY))->pluck('code')->all();
        if ($others !== []) {
            throw new RuntimeException('Refusing to drop the locale registry: ad_language has other languages (' . implode(', ', $others) . ').');
        }
        Schema::table('ad_language', function (Blueprint $table) {
            $table->dropColumn(self::COLUMNS);
        });
        $this->flushRegistryCache();
    }

    private function flushRegistryCache(): void
    {
        if (class_exists(\App\Services\Locale\LocaleRegistry::class)) {
            \App\Services\Locale\LocaleRegistry::flush();
        }
    }
};
