<?php

namespace Tests\Feature\Admin;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Bootstrap\BootProviders;
use Modules\BackEnd\Entities\AdUser;
use Tests\TestCase;

/**
 * In-memory admin for the phase 1E tests: en/vi legacy + ja 'editing' + ko 'disabled' in the registry before the
 * routes are built (admin content-language routes come from the registry at boot), the admin tables the master
 * layout reads, and the super administrator (backend.adUserAdmin) signed in.
 */
abstract class AdminTestCase extends TestCase
{
    public function createApplication()
    {
        $app = require __DIR__ . '/../../../bootstrap/app.php';
        $app->beforeBootstrapping(BootProviders::class, function ($app) {
            $db = $app['db']->connection();
            $schema = $db->getSchemaBuilder();
            $schema->create('ad_language', function (Blueprint $t) {
                $t->increments('id');
                $t->string('code', 50);
                $t->string('name')->nullable();
                $t->string('short_name')->nullable();
                $t->string('image_link')->nullable();
                $t->string('currency_format')->nullable();
                $t->boolean('is_default')->default(false);
                $t->unsignedInteger('ord')->default(0);
            });
            $db->table('ad_language')->insert([
                ['id' => 1, 'code' => 'vi', 'name' => 'Tiếng Việt', 'is_default' => false, 'ord' => 2],
                ['id' => 2, 'code' => 'en', 'name' => 'English', 'is_default' => true, 'ord' => 1],
            ]);
            (require $app->databasePath('migrations/2026_11_01_000001_add_locale_registry_to_ad_language.php'))->up();
            $db->table('ad_language')->insert([
                ['id' => 3, 'code' => 'ja', 'name' => 'Japanese', 'native_name' => '日本語', 'english_name' => 'Japanese', 'ord' => 3, 'site_status' => 'editing', 'route_style' => 'intl', 'url_prefix' => 'ja', 'workflow_controlled' => true],
                ['id' => 4, 'code' => 'ko', 'name' => 'Korean', 'native_name' => '한국어', 'english_name' => 'Korean', 'ord' => 4, 'site_status' => 'disabled', 'route_style' => 'intl', 'url_prefix' => 'ko', 'workflow_controlled' => true],
            ]);
            $schema->create('ad_resource', function (Blueprint $t) {
                $t->increments('id');
                $t->string('name')->default('');
                $t->string('alias');
                $t->unsignedInteger('ord')->default(0);
                $t->dateTime('created_at')->nullable();
                $t->unsignedInteger('created_by')->default(1);
            });
            $schema->create('ad_privilege', function (Blueprint $t) {
                $t->increments('id');
                $t->unsignedInteger('resource_id');
                $t->string('name')->default('');
                $t->string('alias');
                $t->unsignedInteger('ord')->default(0);
                $t->dateTime('created_at')->nullable();
                $t->unsignedInteger('created_by')->default(1);
            });
            $schema->create('ad_role', function (Blueprint $t) {
                $t->increments('id');
                $t->string('name');
                $t->dateTime('created_at')->nullable();
                $t->unsignedInteger('created_by')->default(1);
            });
            $schema->create('ad_role_privilege', function (Blueprint $t) {
                $t->unsignedInteger('role_id');
                $t->unsignedInteger('privilege_id');
            });
            $schema->create('ad_user_role', function (Blueprint $t) {
                $t->unsignedInteger('user_id');
                $t->unsignedInteger('role_id');
            });
            foreach (['config', 'cruise', 'cabin', 'itinerary', 'article', 'destination', 'faq', 'group', 'service', 'page-config', 'cabin-manager'] as $i => $resource) {
                $db->table('ad_resource')->insert(['id' => $i + 1, 'alias' => $resource]);
                foreach (['read', 'create', 'update', 'delete'] as $privilege) {
                    $db->table('ad_privilege')->insert(['alias' => $privilege, 'resource_id' => $i + 1]);
                }
            }
            // The existing roles of the site, before the editorial privileges exist.
            $db->table('ad_role')->insert([['id' => 1, 'name' => 'Quản trị hệ thống'], ['id' => 2, 'name' => 'Biên tập viên']]);
            $configUpdate = $db->table('ad_privilege')->where('resource_id', 1)->where('alias', 'update')->value('id');
            $db->table('ad_role_privilege')->insert(['role_id' => 2, 'privilege_id' => $configUpdate]);
            $db->table('ad_role_privilege')->insert($db->table('ad_privilege')->where('alias', 'update')->pluck('id')->map(fn ($p) => ['role_id' => 2, 'privilege_id' => $p])->unique()->values()->all());
            // 1E hardening: editorial privileges and roles (gates are defined at boot from ad_privilege).
            (require $app->databasePath('migrations/2026_11_05_000001_add_editorial_permissions_and_audit.php'))->up();
            $schema->create('ad_user', function (Blueprint $t) {
                $t->increments('id');
                $t->string('username')->nullable();
                $t->string('password')->nullable();
                $t->string('fullname')->nullable();
                $t->string('email')->nullable();
                $t->string('avatar')->nullable();
                $t->string('cover')->nullable();
                $t->tinyInteger('theme')->default(0);
                $t->tinyInteger('status')->default(1);
                $t->string('provider')->nullable();
                $t->string('remember_token')->nullable();
                $t->timestamps();
                $t->unsignedInteger('created_by')->nullable();
                $t->unsignedInteger('updated_by')->nullable();
            });
            $db->table('ad_user')->insert([
                ['id' => 1, 'username' => 'admin', 'fullname' => 'Admin', 'status' => 1],
                ['id' => 2, 'username' => 'translator', 'fullname' => 'Translator', 'status' => 1],
                ['id' => 3, 'username' => 'reviewer', 'fullname' => 'Reviewer', 'status' => 1],
                ['id' => 4, 'username' => 'legal', 'fullname' => 'Legal', 'status' => 1],
                ['id' => 5, 'username' => 'publisher', 'fullname' => 'Publisher', 'status' => 1],
                ['id' => 6, 'username' => 'editor', 'fullname' => 'Legacy EN/VI editor', 'status' => 1],
                ['id' => 7, 'username' => 'localeadmin', 'fullname' => 'Locale admin', 'status' => 1],
            ]);
            $schema->create('ad_menu', function (Blueprint $t) {
                $t->increments('id');
                $t->unsignedInteger('parent_id')->nullable();
                $t->unsignedInteger('privilege_id')->nullable();
                $t->string('name')->nullable();
                $t->string('url')->nullable();
                $t->text('active_url')->nullable();
                $t->boolean('is_multi_language')->default(false);
                $t->string('icon')->nullable();
                $t->unsignedInteger('ord')->default(0);
                $t->tinyInteger('status')->default(1);
                $t->timestamps();
            });
            $schema->create('ad_config', function (Blueprint $t) {
                $t->increments('id');
                $t->unsignedInteger('language_id')->nullable();
                $t->string('label')->nullable();
                $t->string('key')->nullable()->unique();
                $t->text('value')->nullable();
                $t->text('list_value')->nullable();
                $t->tinyInteger('type')->default(0);
                $t->unsignedInteger('ord')->default(0);
                $t->dateTime('updated_at')->nullable();
                $t->unsignedInteger('updated_by')->nullable();
            });
            $schema->create('ad_logging', function (Blueprint $t) {
                $t->increments('id');
                $t->unsignedInteger('user_id')->nullable();
                $t->string('action')->nullable();
                $t->text('detail')->nullable();
                $t->string('ip')->nullable();
                $t->string('user_agent')->nullable();
                $t->string('type')->nullable();
                $t->timestamps();
            });
            $this->beforeBoot($app);
        });
        $app->make(Kernel::class)->bootstrap();

        return $app;
    }

    /** Extra tables or migrations a test needs before the providers boot (admin gates are built from ad_privilege at boot). */
    protected function beforeBoot($app): void
    {
    }

    protected function setUp(): void
    {
        parent::setUp();
        \App\Services\Locale\LocaleRegistry::flush();
        $this->actingAs(AdUser::find(1), 'admin');
    }

    /** Assigns editorial roles by name (as an administrator would in the user form). */
    protected function giveRoles(int $userId, array $roles): void
    {
        foreach (\Illuminate\Support\Facades\DB::table('ad_role')->whereIn('name', $roles)->pluck('id') as $roleId) {
            \Illuminate\Support\Facades\DB::table('ad_user_role')->insert(['user_id' => $userId, 'role_id' => $roleId]);
        }
    }

    /** The test team: translator, reviewer (translation + SEO), legal, publisher, a legacy editor, a locale admin. */
    protected function seedTeam(): void
    {
        $this->giveRoles(2, ['Biên dịch']);
        $this->giveRoles(3, ['Biên dịch', 'Duyệt bản dịch', 'Duyệt SEO']);
        $this->giveRoles(4, ['Duyệt pháp lý']);
        $this->giveRoles(5, ['Xuất bản bản dịch']);
        $this->giveRoles(6, ['Biên tập viên']);
        $this->giveRoles(7, ['Quản lý ngôn ngữ']);
    }
}
