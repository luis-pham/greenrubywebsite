<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('app_exp_activity', function (Blueprint $table) {
            $table->string('seo_title', 50)->nullable()->after('summary');
            $table->string('seo_description', 255)->nullable()->after('seo_title');
        });
    }

    public function down(): void
    {
        Schema::table('app_exp_activity', function (Blueprint $table) {
            $table->dropColumn(['seo_title', 'seo_description']);
        });
    }
};
