<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE `app_itinerary` ADD COLUMN `seo_title` VARCHAR(65) NULL AFTER `description`');
        DB::statement('ALTER TABLE `app_itinerary` ADD COLUMN `seo_description` VARCHAR(255) NULL AFTER `seo_title`');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE `app_itinerary` DROP COLUMN `seo_description`');
        DB::statement('ALTER TABLE `app_itinerary` DROP COLUMN `seo_title`');
    }
};
