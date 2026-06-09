<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BookingMenuSeeder extends Seeder
{
    /**
     * Thêm mục "Booking & Yêu cầu" vào menu backend.
     */
    public function run()
    {
        $url = '/admincp/booking-manager';
        if (DB::table('ad_menu')->where('url', $url)->exists()) {
            $this->command->info('Menu Booking đã tồn tại.');
            return;
        }

        $cabin = DB::table('ad_menu')->where('url', 'like', '%cabin-manager%')->where('status', 1)->first();
        $parentId = $cabin ? $cabin->parent_id : null;
        $maxOrd = DB::table('ad_menu')->where('parent_id', $parentId)->max('ord');
        $ord = $maxOrd !== null ? (int) $maxOrd + 1 : 1;

        $now = now()->format('Y-m-d H:i:s');
        $adminId = config('backend.adUserAdmin', 1);
        DB::table('ad_menu')->insert([
            'parent_id' => $parentId,
            'privilege_id' => null,
            'name' => 'Booking & Yêu cầu',
            'url' => $url,
            'active_url' => json_encode([
                '/admincp/booking-manager',
                '/admincp/vi/booking-manager',
                '/admincp/en/booking-manager',
            ], JSON_UNESCAPED_UNICODE),
            'is_multi_language' => 1,
            'icon' => 'fas fa-calendar-check',
            'ord' => $ord,
            'status' => 1,
            'created_at' => $now,
            'updated_at' => $now,
            'created_by' => $adminId,
            'updated_by' => $adminId,
        ]);

        $this->command->info('Đã thêm menu "Booking & Yêu cầu".');
    }
}
