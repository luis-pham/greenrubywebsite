<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Number of cabins on board, shown on the fleet cards ("46 private cabins").
        DB::statement('ALTER TABLE `app_cruise` ADD COLUMN `total_cabin` SMALLINT UNSIGNED NULL AFTER `total_floor`');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE `app_cruise` DROP COLUMN `total_cabin`');
    }
};
