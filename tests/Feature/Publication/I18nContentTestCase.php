<?php

namespace Tests\Feature\Publication;

use App\Services\Locale\LocaleRegistry;
use App\Services\Publication\PublicationContext;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Bootstrap\BootProviders;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * In-memory schema for the phase 1C tests: en/vi/ja registry (ja editing, workflow-controlled), the content tables
 * the 1C code reads, and the 1C-2/1C-3 migrations applied on top.
 */
abstract class I18nContentTestCase extends TestCase
{
    public function createApplication()
    {
        $app = require __DIR__ . '/../../../bootstrap/app.php';
        $app->beforeBootstrapping(BootProviders::class, function ($app) {
            $schema = $app['db']->connection()->getSchemaBuilder();
            $schema->create('ad_language', function (Blueprint $t) {
                $t->increments('id');
                $t->string('code', 50);
                $t->string('name')->nullable();
                $t->boolean('is_default')->default(false);
                $t->unsignedInteger('ord')->default(0);
            });
            $app['db']->connection()->table('ad_language')->insert([
                ['id' => 1, 'code' => 'vi', 'name' => 'Tiếng Việt', 'is_default' => false, 'ord' => 2],
                ['id' => 2, 'code' => 'en', 'name' => 'English', 'is_default' => true, 'ord' => 1],
            ]);
            foreach (['ad_resource' => ['alias'], 'ad_privilege' => ['alias']] as $table => $cols) {
                $schema->create($table, function (Blueprint $t) use ($table) {
                    $t->increments('id');
                    $t->string('alias');
                    if ($table === 'ad_privilege') {
                        $t->unsignedInteger('resource_id');
                    }
                });
            }
        });
        $app->make(Kernel::class)->bootstrap();

        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();
        (require database_path('migrations/2026_11_01_000001_add_locale_registry_to_ad_language.php'))->up();
        DB::table('ad_language')->insert(['id' => 3, 'code' => 'ja', 'name' => '日本語', 'ord' => 3, 'site_status' => 'editing', 'route_style' => 'intl', 'workflow_controlled' => true]);
        foreach (['app_cruise', 'app_itinerary', 'app_exp_activity', 'app_group', 'app_category'] as $table) {
            Schema::create($table, function (Blueprint $t) {
                $t->increments('id');
                $t->unsignedInteger('language_id');
                $t->string('name')->nullable();
            });
        }
        Schema::create('app_cabin', function (Blueprint $t) {
            $t->increments('id');
            $t->unsignedInteger('language_id');
            $t->unsignedInteger('cruise_id')->nullable();
            $t->string('name')->nullable();
        });
        Schema::create('app_faq', function (Blueprint $t) {
            $t->increments('id');
            $t->unsignedInteger('language_id');
            $t->unsignedInteger('group_id')->nullable();
            $t->string('question')->nullable();
        });
        DB::table('app_cruise')->insert([['id' => 3, 'language_id' => 2, 'name' => 'Green Ruby 1'], ['id' => 4, 'language_id' => 2, 'name' => 'Green Ruby 2']]);
        DB::table('app_cabin')->insert([
            ['id' => 24, 'language_id' => 2, 'cruise_id' => 3, 'name' => 'Imperial Suite'],
            ['id' => 72, 'language_id' => 2, 'cruise_id' => 4, 'name' => 'Imperial Suite'],
            ['id' => 99, 'language_id' => 2, 'cruise_id' => 0, 'name' => 'Orphan'],
        ]);
        DB::table('app_group')->insert(['id' => 57, 'language_id' => 2, 'name' => 'Bookings']);
        DB::table('app_faq')->insert([['id' => 1, 'language_id' => 2, 'group_id' => 57, 'question' => 'Q1'], ['id' => 2, 'language_id' => 2, 'group_id' => null, 'question' => 'Q2']]);
        (require database_path('migrations/2026_11_02_000001_add_publish_state_to_content_tables.php'))->up();
        (require database_path('migrations/2026_11_02_000002_create_translation_meta_and_link_log_tables.php'))->up();
        Schema::create('app_destination', function (Blueprint $t) {
            $t->increments('id');
            $t->unsignedInteger('language_id');
            $t->string('name')->nullable();
            $t->string('slug', 160)->nullable();
            $t->string('status')->default('published');
        });
        DB::table('app_destination')->insert([
            ['id' => 1, 'language_id' => 2, 'name' => 'Sung Sot Cave', 'slug' => 'sung-sot-cave'],
            ['id' => 2, 'language_id' => 1, 'name' => 'Hang Sửng Sốt', 'slug' => 'hang-sung-sot'],
            ['id' => 17, 'language_id' => 2, 'name' => 'Thien Cung Cave', 'slug' => 'thien-cung-cave'],
        ]);
        (require database_path('migrations/2026_11_03_000001_add_stored_slugs_and_slug_history.php'))->up();
        LocaleRegistry::flush();
        PublicationContext::leave();
    }

}
