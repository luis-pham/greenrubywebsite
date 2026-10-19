<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE `app_cabin` ADD COLUMN `seo_title` VARCHAR(65) NULL AFTER `summary`');
        DB::statement('ALTER TABLE `app_cabin` ADD COLUMN `seo_description` VARCHAR(255) NULL AFTER `seo_title`');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE `app_cabin` DROP COLUMN `seo_description`');
        DB::statement('ALTER TABLE `app_cabin` DROP COLUMN `seo_title`');
    }
};
