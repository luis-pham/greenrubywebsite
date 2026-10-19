<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Evidence status of what an article claims (writing app): verified | ops (needs operations confirmation)
 * | design (design estimate, not measured) | na (service not available yet). Plan items carry a default.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('app_article', function (Blueprint $table) {
            $table->string('evidence_status', 20)->nullable()->after('focus_keyword');
        });
        Schema::table('app_article_plan', function (Blueprint $table) {
            $table->string('evidence', 20)->nullable()->after('note');
        });
    }

    public function down(): void
    {
        Schema::table('app_article', fn (Blueprint $table) => $table->dropColumn('evidence_status'));
        Schema::table('app_article_plan', fn (Blueprint $table) => $table->dropColumn('evidence'));
    }
};
