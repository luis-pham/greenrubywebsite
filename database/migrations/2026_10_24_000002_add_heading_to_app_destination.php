<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Optional H1 for a destination page ("Sung Sot Cave (Surprise Cave): A Visitor's Guide"); cards and maps keep the name. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('app_destination', function (Blueprint $table) {
            $table->string('heading', 160)->nullable()->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('app_destination', function (Blueprint $table) {
            $table->dropColumn('heading');
        });
    }
};
