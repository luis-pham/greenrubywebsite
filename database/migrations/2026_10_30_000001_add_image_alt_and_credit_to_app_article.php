<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cover image alt text and credit for articles (e.g. "Photo: Jakub Hałun, CC BY 4.0, via Wikimedia Commons"),
 * as destinations already have. Both optional: without alt text the frontend falls back to the title.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('app_article', function (Blueprint $table) {
            $table->string('image_alt', 255)->nullable()->after('image_link');
            $table->string('image_credit', 255)->nullable()->after('image_alt');
        });
    }

    public function down(): void
    {
        Schema::table('app_article', function (Blueprint $table) {
            $table->dropColumn(['image_alt', 'image_credit']);
        });
    }
};
