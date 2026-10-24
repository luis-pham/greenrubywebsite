<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Show/hide switch for the Next.js-only hubs (destinations, travel guides), per language. Hidden pages 404,
 * leave the sitemaps, footer and llms.txt, and links to them render as plain text.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('app_page', function (Blueprint $table) {
            $table->boolean('is_visible')->default(true)->after('seo_description');
        });
    }

    public function down(): void
    {
        Schema::table('app_page', function (Blueprint $table) {
            $table->dropColumn('is_visible');
        });
    }
};
