<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $tables = [
        'app_page',
        'app_exp_activity',
        'app_article',
        'app_category',
        'app_group',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            DB::statement("ALTER TABLE `{$table}` MODIFY `seo_title` VARCHAR(65) NULL");
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            DB::statement("ALTER TABLE `{$table}` MODIFY `seo_title` VARCHAR(50) NULL");
        }
    }
};
