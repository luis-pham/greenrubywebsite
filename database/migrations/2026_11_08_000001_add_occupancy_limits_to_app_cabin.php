<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Maximum guest combinations per cabin (owner 2026-11-02, from the agent contract III.2): "2+2, 3+0" = at most 2 adults
 * with 2 children, or 3 adults with no child; children count every age, infants included. The per-type over_capacity_*
 * limits cannot say "the third adult takes the children's place", so a cabin with this column set is validated by it
 * alone (App\Support\Occupancy); NULL keeps today's per-type rule. Additive, nullable, reversible.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('app_cabin', 'occupancy_limits')) {
            Schema::table('app_cabin', function (Blueprint $t) {
                $t->string('occupancy_limits', 100)->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('app_cabin', 'occupancy_limits')) {
            Schema::table('app_cabin', function (Blueprint $t) {
                $t->dropColumn('occupancy_limits');
            });
        }
    }
};
