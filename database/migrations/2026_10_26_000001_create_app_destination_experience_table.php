<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Experiences (app_exp_activity) that take place at a destination, chosen by editors in the destination form,
 * so experience and destination pages can link each other (docs/seo-audit/15-information-architecture-proposal).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_destination_experience', function (Blueprint $table) {
            $table->unsignedInteger('destination_id');
            $table->unsignedInteger('experience_id');
            $table->primary(['destination_id', 'experience_id']);
            $table->index('experience_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_destination_experience');
    }
};
