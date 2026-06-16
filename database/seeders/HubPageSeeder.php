<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HubPageSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            ['code' => 'itinerary', 'language_id' => 1, 'title' => 'Hành trình'],
            ['code' => 'itinerary', 'language_id' => 2, 'title' => 'Itinerary'],
            ['code' => 'contact', 'language_id' => 1, 'title' => 'Liên hệ'],
            ['code' => 'contact', 'language_id' => 2, 'title' => 'Contact'],
            ['code' => 'booking', 'language_id' => 1, 'title' => 'Đặt tour'],
            ['code' => 'booking', 'language_id' => 2, 'title' => 'Booking'],
        ];

        $now = now();
        $createdBy = 1;

        foreach ($pages as $page) {
            $exists = DB::table('app_page')
                ->where('code', $page['code'])
                ->where('language_id', $page['language_id'])
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('app_page')->insert([
                'language_id' => $page['language_id'],
                'code' => $page['code'],
                'title' => $page['title'],
                'description' => null,
                'seo_title' => null,
                'seo_description' => null,
                'created_at' => $now,
                'created_by' => $createdBy,
                'updated_at' => null,
                'updated_by' => null,
            ]);
        }
    }
}
