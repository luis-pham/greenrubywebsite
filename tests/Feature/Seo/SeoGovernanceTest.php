<?php

namespace Tests\Feature\Seo;

use App\Http\Controllers\Api\Editor\PlanController;
use App\Http\Controllers\Api\Editor\SeoController;
use App\Services\Seo\SeoGuard;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Bootstrap\BootProviders;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Modules\BackEnd\Entities\AdUser;
use Tests\TestCase;

/**
 * Keyword ownership (G1), cannibalization checks against the inventory (G2) and the guard with its override
 * permission and audit log (G3), on an in-memory database holding only the tables the guard reads.
 */
class SeoGovernanceTest extends TestCase
{
    private const ADMIN = 1;  // role 1 Quản trị hệ thống
    private const EDITOR = 2; // role 2 Biên tập viên

    /** The back-office module reads ad_language and the privileges while the app boots, so those tables come first. */
    public function createApplication()
    {
        $app = require __DIR__ . '/../../../bootstrap/app.php';
        $app->beforeBootstrapping(BootProviders::class, function ($app) {
            $schema = $app['db']->connection()->getSchemaBuilder();
            $schema->create('ad_language', function (Blueprint $t) {
                $t->increments('id');
                $t->string('code', 5);
                $t->boolean('is_default')->default(false);
                $t->unsignedInteger('ord')->default(0);
            });
            $schema->create('ad_resource', function (Blueprint $t) {
                $t->increments('id');
                $t->string('alias');
            });
            $schema->create('ad_privilege', function (Blueprint $t) {
                $t->increments('id');
                $t->unsignedInteger('resource_id');
                $t->string('alias');
            });
        });
        $app->make(Kernel::class)->bootstrap();

        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();
        config(['seo.override_roles' => [1], 'seo.guard' => true]);
        (require database_path('migrations/2026_10_25_000001_create_seo_governance_tables.php'))->up();

        Schema::create('ad_user_role', function (Blueprint $t) {
            $t->unsignedInteger('user_id');
            $t->unsignedInteger('role_id');
        });
        Schema::create('ad_user', function (Blueprint $t) {
            $t->increments('id');
            $t->string('username')->nullable();
        });
        Schema::create('app_page', function (Blueprint $t) {
            $t->increments('id');
            $t->string('code');
            $t->unsignedInteger('language_id');
            $t->boolean('is_visible')->default(true);
        });
        Schema::create('app_category', function (Blueprint $t) {
            $t->increments('id');
            $t->unsignedInteger('language_id');
            $t->string('slug');
            $t->unsignedInteger('type')->nullable();
        });
        Schema::create('app_article', function (Blueprint $t) {
            $t->increments('id');
            $t->unsignedInteger('language_id');
            $t->unsignedInteger('category_id')->nullable();
            $t->string('title');
            $t->string('seo_title')->nullable();
            $t->string('focus_keyword')->nullable();
            $t->string('slug')->nullable();
            $t->boolean('is_published')->default(false);
            $t->dateTime('publish_date')->nullable();
            $t->longText('content')->nullable();
            $t->json('ai_meta')->nullable();
        });
        Schema::create('app_destination', function (Blueprint $t) {
            $t->increments('id');
            $t->unsignedInteger('language_id');
            $t->string('slug');
            $t->string('name');
            $t->string('heading')->nullable();
            $t->string('status')->default('draft');
            $t->longText('content')->nullable();
        });
        Schema::create('app_article_plan', function (Blueprint $t) {
            $t->increments('id');
            $t->string('code')->nullable();
            $t->string('lang', 2);
            $t->string('title');
            $t->string('primary_kw')->nullable();
            $t->text('secondary_kw')->nullable();
            $t->char('intent', 1)->nullable();
            $t->string('status')->default('idea');
            $t->unsignedInteger('article_id')->nullable();
            $t->string('ai_status')->nullable();
            $t->text('ai_error')->nullable();
            $t->dateTime('ai_queued_at')->nullable();
            $t->dateTime('ai_finished_at')->nullable();
            $t->timestamp('updated_at')->nullable();
        });
        Schema::create('app_group', function (Blueprint $t) {
            $t->increments('id');
            $t->string('slug');
        });
        Schema::create('app_cabin', function (Blueprint $t) {
            $t->increments('id');
            $t->unsignedInteger('language_id');
            $t->unsignedInteger('group_id');
            $t->unsignedInteger('cruise_id')->nullable();
            $t->string('name');
            $t->unsignedInteger('ord')->default(0);
        });

        DB::table('ad_language')->insert([['id' => 1, 'code' => 'vi'], ['id' => 2, 'code' => 'en']]);
        DB::table('ad_user')->insert([['id' => 10, 'username' => 'admin'], ['id' => 20, 'username' => 'editor']]);
        DB::table('ad_user_role')->insert([['user_id' => 10, 'role_id' => self::ADMIN], ['user_id' => 20, 'role_id' => self::EDITOR]]);
        DB::table('app_page')->insert(['code' => 'destinations', 'language_id' => 2, 'is_visible' => false]);
        DB::table('app_seo_owner')->insert([
            'language_id' => 2, 'keyword' => 'hanoi to ha long bay', 'keyword_key' => 'hanoi to ha long bay', 'intent' => 'I',
            'owner_url' => '/guides/hanoi-to-halong-bay', 'owner_type' => 'guide', 'page_role' => 'pillar', 'status' => 'active',
            'supporting_keywords' => json_encode(['hanoi to lan ha bay']),
        ]);
        DB::table('app_destination')->insert(['language_id' => 2, 'slug' => 'sung-sot-cave', 'name' => 'Sung Sot Cave', 'status' => 'draft']);
    }

    public function testRegistryOwnerMakesTheSameKeywordAConflict()
    {
        $r = SeoGuard::check($this->keyword('Hanoi to Halong Bay', 'I'));
        $this->assertSame('conflict', $r['verdict']);
        $this->assertSame('update_existing', $r['recommendation']);
        $this->assertSame('/guides/hanoi-to-halong-bay', $r['target']['url']);

        // A supporting keyword of the owner is owned too.
        $this->assertSame('conflict', SeoGuard::check($this->keyword('Hanoi to Lan Ha Bay', 'I'))['verdict']);
    }

    public function testHiddenDestinationStillOwnsItsName()
    {
        $r = SeoGuard::check($this->keyword('Sung Sot Cave guide', 'I'));
        $this->assertSame('conflict', $r['verdict']);
        $this->assertSame('/destinations/sung-sot-cave', $r['target']['url']);
        $this->assertSame('hidden', $r['target']['status']);
    }

    public function testDraftArticleIsInTheInventory()
    {
        DB::table('app_article')->insert(['language_id' => 2, 'title' => 'Ha Long Bay typhoon season', 'focus_keyword' => 'ha long bay typhoon season']);
        $r = SeoGuard::check($this->keyword('halong bay typhoon season', 'I'));
        $this->assertSame('conflict', $r['verdict']);
        $this->assertSame('merge', $r['recommendation']);
    }

    public function testEditorCannotOverrideEvenWithAReason()
    {
        $gate = SeoGuard::gate('publish', 'article', 5, $this->keyword('hanoi to ha long bay', 'I'), 20, 'Bài này khác góc nhìn hoàn toàn.');
        $this->assertFalse($gate['allowed']);
        $this->assertSame('blocked', $gate['outcome']);
        $this->assertFalse($gate['canOverride']);
        $this->assertDatabaseHas('app_seo_guard_log', ['id' => $gate['logId'], 'outcome' => 'blocked', 'override_reason' => null, 'user_id' => 20]);
    }

    public function testAdminNeedsAReasonToOverride()
    {
        $blocked = SeoGuard::gate('publish', 'article', 5, $this->keyword('hanoi to ha long bay', 'I'), 10);
        $this->assertSame('blocked', $blocked['outcome']);
        $this->assertTrue($blocked['canOverride']);

        $gate = SeoGuard::gate('publish', 'article', 5, $this->keyword('hanoi to ha long bay', 'I'), 10, 'Trang cũ sẽ được gộp vào bài này.');
        $this->assertTrue($gate['allowed']);
        $this->assertSame('overridden', $gate['outcome']);
        $this->assertDatabaseHas('app_seo_guard_log', ['id' => $gate['logId'], 'outcome' => 'overridden', 'override_reason' => 'Trang cũ sẽ được gộp vào bài này.', 'user_id' => 10]);
        $this->assertSame(2, DB::table('app_seo_guard_log')->count());
    }

    public function testAnOverrideCarriesOverToLaterChecksOfTheSameKeyword()
    {
        SeoGuard::gate('plan_start', 'plan', 7, $this->keyword('hanoi to ha long bay', 'I'), 10, 'Đã thống nhất viết bản cho khách đi riêng.');
        $later = SeoGuard::gate('ai_queue', 'plan', 7, $this->keyword('Hanoi to Halong Bay', 'I'), null);
        $this->assertSame('overridden', $later['outcome']);
        // Another plan item with the same keyword is not covered.
        $this->assertSame('blocked', SeoGuard::gate('ai_queue', 'plan', 8, $this->keyword('hanoi to ha long bay', 'I'), null)['outcome']);
    }

    public function testClearAndHealthyChecksAreAllowedAndLogged()
    {
        $gate = SeoGuard::gate('publish', 'article', 6, $this->keyword('ha long bay cruise wifi', 'I'), 20);
        $this->assertTrue($gate['allowed']);
        $this->assertSame('allowed', $gate['outcome']);
        $this->assertDatabaseHas('app_seo_guard_log', ['id' => $gate['logId'], 'outcome' => 'allowed']);
    }

    public function testAiQueueSkipsBlockedPlanItems()
    {
        $this->actingAsAdminUser(20);
        DB::table('app_article_plan')->insert([
            ['id' => 1, 'code' => 'A1', 'lang' => 'en', 'title' => 'Hanoi to Halong Bay', 'primary_kw' => 'hanoi to halong bay', 'intent' => 'I', 'status' => 'idea'],
            ['id' => 2, 'code' => 'A2', 'lang' => 'en', 'title' => 'Cruise wifi', 'primary_kw' => 'ha long bay cruise wifi', 'intent' => 'I', 'status' => 'idea'],
        ]);
        $body = app(PlanController::class)->aiQueue(new Request(['ids' => [1, 2]]))->getData(true);

        $this->assertSame(1, $body['queued']);
        $this->assertSame([1], array_column($body['blocked'], 'id'));
        $this->assertNull(DB::table('app_article_plan')->where('id', 1)->value('ai_status'));
        $this->assertSame('queued', DB::table('app_article_plan')->where('id', 2)->value('ai_status'));
    }

    public function testPlanOverrideEndpointIsForAdminsOnly()
    {
        DB::table('app_article_plan')->insert(['id' => 3, 'code' => 'A3', 'lang' => 'en', 'title' => 'Hanoi to Ha Long', 'primary_kw' => 'hanoi to ha long bay', 'intent' => 'I']);
        $reason = new Request(['reason' => 'Bản dành riêng cho khách đi xe riêng.']);

        $this->actingAsAdminUser(20);
        $this->assertSame(403, app(SeoController::class)->overridePlan($reason, 3)->getStatusCode());
        $this->assertSame(0, DB::table('app_seo_guard_log')->count());

        $this->actingAsAdminUser(10);
        $res = app(SeoController::class)->overridePlan($reason, 3);
        $this->assertSame(200, $res->getStatusCode());
        $this->assertSame('overridden', $res->getData(true)['outcome']);
        $this->assertTrue(SeoGuard::hasOverride('plan', 3, 'Hanoi to Halong Bay'));
    }

    public function testRegistryRejectsAnEquivalentKeywordWithTheSameIntent()
    {
        $this->actingAsAdminUser(10);
        $this->expectException(ValidationException::class);
        app(SeoController::class)->storeOwner(new Request([
            'lang' => 'en', 'keyword' => 'Hanoi to Halong Bay', 'intent' => 'I', 'ownerUrl' => '/blog/x.html', 'status' => 'active',
        ]));
    }

    public function testEditorCannotEditTheRegistry()
    {
        $this->actingAsAdminUser(20);
        try {
            app(SeoController::class)->storeOwner(new Request(['lang' => 'en', 'keyword' => 'ha long bay caves', 'intent' => 'I', 'ownerUrl' => '/destinations', 'status' => 'active']));
            $this->fail('An editor must not write the registry.');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        }
        $this->assertSame(1, DB::table('app_seo_owner')->count());
    }

    private function keyword(string $keyword, ?string $intent): array
    {
        return ['language_id' => 2, 'keyword' => $keyword, 'secondary' => [], 'intent' => $intent, 'title' => $keyword, 'content' => '', 'self' => []];
    }

    private function actingAsAdminUser(int $id): void
    {
        $user = new AdUser();
        $user->id = $id;
        Auth::guard('admin')->setUser($user);
    }
}
