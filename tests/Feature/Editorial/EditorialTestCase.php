<?php

namespace Tests\Feature\Editorial;

use App\Services\Content\ContentTranslations;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Feature\Admin\AdminTestCase;

/**
 * Phase 1E: the content tables with the real column set (schema.php, generated from the migrated clone) and a small
 * EN/VI catalogue whose pairs are explicit links, as in production. ja is 'editing' (AdminTestCase); en/vi own the
 * price lists (config booking.price_lists), ja borrows en.
 */
abstract class EditorialTestCase extends AdminTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        foreach (require __DIR__ . '/schema.php' as $table => $columns) {
            Schema::create($table, function (Blueprint $t) use ($table, $columns) {
                foreach ($columns as [$name, $type, $nullable, $default, $auto]) {
                    if ($auto) {
                        $t->bigIncrements($name);
                        continue;
                    }
                    $column = match ($type) {
                        'int', 'smallint', 'tinyint', 'bigint', 'mediumint' => $t->integer($name),
                        'double', 'decimal', 'float' => $t->float($name),
                        'date' => $t->date($name),
                        'datetime', 'timestamp' => $t->dateTime($name),
                        'time' => $t->time($name),
                        'json', 'text', 'longtext', 'mediumtext' => $t->text($name),
                        default => $t->string($name, 255),
                    };
                    $nullable ? $column->nullable() : null;
                    if ($default !== null && !in_array($default, ['CURRENT_TIMESTAMP', 'current_timestamp()'], true)) {
                        $column->default(trim($default, "'"));
                    }
                }
                if ($table === 'content_translations') {
                    $t->unique(['entity_type', 'entity_id']);
                    $t->unique(['entity_type', 'group_key', 'language_id']);
                }
                if ($table === 'translation_meta') {
                    $t->primary(['entity_type', 'entity_id']);
                }
            });
        }
        $this->seedCatalogue();
        ContentTranslations::flush();
    }

    protected function seedCatalogue(): void
    {
        $stamp = ['created_at' => now(), 'created_by' => 1];
        DB::table('app_cruise')->insert([
            ['id' => 3, 'language_id' => 2, 'name' => 'Green Ruby 1', 'summary' => 'Ha Long eco-luxury', 'content' => 'EN body', 'image_link' => '/gr1.jpg', 'capacity' => 92, 'total_floor' => 4, 'total_cabin' => 46, 'dimension_length' => 88, 'year_built' => 2026, 'slug' => 'green-ruby-1', 'publish_state' => 'published'] + $stamp,
            ['id' => 1, 'language_id' => 1, 'name' => 'Green Ruby 1', 'summary' => 'Hạ Long', 'content' => 'VI body', 'image_link' => '/gr1.jpg', 'capacity' => 92, 'total_floor' => 4, 'total_cabin' => 46, 'dimension_length' => 88, 'year_built' => 2026, 'slug' => 'green-ruby-1', 'publish_state' => 'published'] + $stamp,
        ]);
        // Structural groups (not translation groups): cabin facility type "accommodation" per language.
        DB::table('app_group')->insert([
            ['id' => 29, 'language_id' => 2, 'name' => 'Accommodation', 'slug' => 'accommodation', 'ord' => 1, 'type' => 2, 'publish_state' => 'published'] + $stamp,
            ['id' => 8, 'language_id' => 1, 'name' => 'Phòng ở', 'slug' => 'phong-o', 'ord' => 1, 'type' => 2, 'publish_state' => 'published'] + $stamp,
        ]);
        DB::table('app_page')->insert(['id' => 1, 'language_id' => 2, 'code' => 'homepage', 'title' => 'Home', 'is_visible' => 1] + $stamp);
        DB::table('ad_config')->insert([['language_id' => 2, 'key' => 'website-name-en', 'value' => 'Green Ruby Cruises'], ['language_id' => 1, 'key' => 'website-name-vi', 'value' => 'Green Ruby'], ['language_id' => 2, 'key' => 'shared-flag', 'value' => '1']]);
        DB::table('app_page_section')->insert(['id' => 1, 'page_id' => 1, 'name' => 'Banner', 'ord' => 1] + $stamp);
        DB::table('app_page_config')->insert(['page_id' => 1, 'section_id' => 1, 'label' => 'Title', 'key' => 'banner_title', 'value' => 'Sail Ha Long', 'type' => 1, 'ord' => 1]);
        DB::table('app_cabin')->insert([
            ['id' => 24, 'language_id' => 2, 'cruise_id' => 3, 'group_id' => 29, 'name' => 'Imperial Suite', 'summary' => 'Suite', 'view' => 'Ocean View', 'capacity' => 3, 'area' => 120, 'discount_percent' => 0, 'ord' => 1, 'slug' => 'imperial-suite', 'publish_state' => 'published'] + $stamp,
            ['id' => 38, 'language_id' => 1, 'cruise_id' => 1, 'group_id' => 8, 'name' => 'Imperial Suite', 'summary' => 'Suite', 'view' => 'Hướng biển', 'capacity' => 2, 'area' => 120, 'discount_percent' => 0, 'ord' => 1, 'slug' => 'imperial-suite', 'publish_state' => 'published'] + $stamp,
        ]);
        DB::table('app_cabin_room')->insert(['cabin_id' => 24, 'title' => 'Bedroom', 'description' => 'King bed', 'ord' => 1] + $stamp);
        DB::table('app_cabin_price')->insert([['cabin_id' => 24, 'duration' => 2, 'guest' => 2, 'price' => 900], ['cabin_id' => 38, 'duration' => 2, 'guest' => 2, 'price' => 22000000]]);
        DB::table('app_itinerary')->insert([
            ['id' => 3, 'language_id' => 2, 'name' => 'Halong Immersion', 'description' => '3 days', 'duration' => 3, 'bay' => 1, 'destination' => '[]', 'slug' => 'the-halong-immersion', 'publish_state' => 'published'] + $stamp,
            ['id' => 8, 'language_id' => 1, 'name' => 'Đậm chất Hạ Long', 'description' => '3 ngày', 'duration' => 3, 'bay' => 1, 'destination' => '[]', 'slug' => 'dam-chat-ha-long', 'publish_state' => 'published'] + $stamp,
        ]);
        DB::table('app_itinerary_day')->insert(['id' => 1, 'itinerary_id' => 3, 'day' => 1] + $stamp);
        DB::table('app_itinerary_day_detail')->insert(['itinerary_day_id' => 1, 'time' => '12:00', 'title' => 'Boarding', 'description' => 'Welcome drink', 'ord' => 1] + $stamp);
        DB::table('app_cruise_itinerary')->insert([['cruise_id' => 3, 'itinerary_id' => 3, 'start_at' => '2027-03-10'], ['cruise_id' => 3, 'itinerary_id' => 3, 'start_at' => '2027-03-17'], ['cruise_id' => 1, 'itinerary_id' => 8, 'start_at' => '2027-03-11']]);
        DB::table('app_category')->insert([
            ['id' => 9, 'language_id' => 2, 'parent_id' => null, 'name' => 'Root', 'slug' => 'root', 'lvl' => 0, 'lft' => 0, 'rgt' => 3, 'type' => 1, 'publish_state' => 'published'] + $stamp,
            ['id' => 10, 'language_id' => 2, 'parent_id' => 9, 'name' => 'Travel tips', 'slug' => 'travel-tips', 'lvl' => 1, 'lft' => 1, 'rgt' => 2, 'type' => 1, 'publish_state' => 'published'] + $stamp,
            ['id' => 19, 'language_id' => 1, 'parent_id' => null, 'name' => 'Root', 'slug' => 'root', 'lvl' => 0, 'lft' => 0, 'rgt' => 3, 'type' => 1, 'publish_state' => 'published'] + $stamp,
            ['id' => 20, 'language_id' => 1, 'parent_id' => 19, 'name' => 'Hành trình', 'slug' => 'hanh-trinh', 'lvl' => 1, 'lft' => 1, 'rgt' => 2, 'type' => 1, 'publish_state' => 'published'] + $stamp,
        ]);
        DB::table('app_article')->insert([
            ['id' => 100, 'language_id' => 2, 'category_id' => 10, 'title' => 'Best time to visit', 'slug' => 'best-time', 'lead' => 'Lead', 'content' => 'EN', 'publish_date' => '2026-10-01', 'is_featured' => 0, 'is_published' => 1] + $stamp,
            ['id' => 200, 'language_id' => 1, 'category_id' => 20, 'title' => 'Mùa nào đẹp', 'slug' => 'mua-nao-dep', 'lead' => 'Lead', 'content' => 'VI', 'publish_date' => '2026-10-01', 'is_featured' => 0, 'is_published' => 1] + $stamp,
        ]);
        DB::table('app_group')->insert([
            ['id' => 57, 'language_id' => 2, 'name' => 'Bookings', 'slug' => 'bookings', 'ord' => 1, 'type' => 1, 'publish_state' => 'published'] + $stamp,
            ['id' => 58, 'language_id' => 1, 'name' => 'Đặt chỗ', 'slug' => 'dat-cho', 'ord' => 1, 'type' => 1, 'publish_state' => 'published'] + $stamp,
        ]);
        DB::table('app_faq')->insert(['id' => 1, 'language_id' => 2, 'group_id' => 57, 'question' => 'Can I cancel?', 'answer' => 'Yes', 'ord' => 1, 'publish_state' => 'published'] + $stamp);
        DB::table('app_destination')->insert([
            ['id' => 1, 'language_id' => 2, 'slug' => 'sung-sot-cave', 'name' => 'Sung Sot Cave', 'bay' => 'ha-long', 'category' => 'cave', 'status' => 'published'],
            ['id' => 2, 'language_id' => 1, 'slug' => 'hang-sung-sot', 'name' => 'Hang Sửng Sốt', 'bay' => 'ha-long', 'category' => 'cave', 'status' => 'published'],
        ]);
        DB::table('app_service')->insert(['id' => 5, 'language_id' => 2, 'name' => 'Spa', 'description' => '60 min', 'price' => 30, 'type' => 1, 'status' => 1] + $stamp);
        DB::table('app_amenity')->insert(['id' => 7, 'language_id' => 2, 'name' => 'Wi-Fi', 'ord' => 1] + $stamp);
        foreach (['cruise' => [3, 1], 'cabin' => [24, 38], 'itinerary' => [3, 8], 'article_category' => [10, 20], 'article' => [100, 200], 'faq_group' => [57, 58], 'destination' => [1, 2]] as $type => $pair) {
            ContentTranslations::link($type, $pair);
        }
        DB::table('content_translation_log')->delete(); // fixture links are not editor history
    }

    protected function ja(): \Modules\BackEnd\Entities\AdLanguage
    {
        return \App\Services\Locale\LocaleRegistry::byCode('ja');
    }

    protected function groupOf(string $type, int $id): array
    {
        $key = DB::table('content_translations')->where('entity_type', $type)->where('entity_id', $id)->value('group_key');

        return DB::table('content_translations')->where('group_key', $key)->orderBy('language_id')->pluck('entity_id', 'language_id')->all();
    }
}
