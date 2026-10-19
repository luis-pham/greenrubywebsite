<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** FAQs shown on one itinerary page (picked from the FAQ module in the itinerary form). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_itinerary_faq', function (Blueprint $table) {
            $table->unsignedInteger('itinerary_id');
            $table->unsignedInteger('faq_id');
            $table->unsignedInteger('ord')->default(0);
            $table->primary(['itinerary_id', 'faq_id']);
            $table->index('faq_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_itinerary_faq');
    }
};
