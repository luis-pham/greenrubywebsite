<?php

namespace Tests\Feature\Careers;

use App\Http\Middleware\RevalidateFrontend;
use App\Services\Content\CareerContentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Tests\Feature\Admin\AdminTestCase;

/** Recruitment (/tuyen-dung): admin module, permissions, public payloads, sitemap. */
class CareersTest extends AdminTestCase
{
    protected function beforeBoot($app): void
    {
        // Production ad_menu has created_by (the in-memory admin schema does not).
        $app['db']->connection()->getSchemaBuilder()->table('ad_menu', fn ($t) => $t->unsignedInteger('created_by')->nullable());
        // Article "update" privilege for role 2 exists (AdminTestCase), so the migration grants "job" to that role.
        (require $app->databasePath('migrations/2026_11_17_000001_create_app_job_table.php'))->up();
    }

    private function job(array $attributes): int
    {
        return (int) DB::table('app_job')->insertGetId(array_merge([
            'title' => 'Sales OTA', 'department' => 'Kinh doanh', 'status' => 'published', 'posted_at' => '2026-11-01',
            'description' => '<p>Mô tả</p>', 'ord' => 1, 'created_at' => now(),
        ], $attributes));
    }

    private function api(string $path)
    {
        config(['services.content_api.key' => 'test-key']);

        return $this->withHeaders(['X-Content-Key' => 'test-key'])->get('/api/v1/content' . $path);
    }

    public function test_migration_adds_permissions_menu_and_recruitment_email(): void
    {
        $this->assertSame('hrm@greenrubycruises.com', DB::table('ad_config')->where('key', 'recruitment-email')->value('value'));
        $this->assertTrue(DB::table('ad_menu')->where('url', '/admincp/job')->exists());
        $roles = DB::table('ad_role_privilege')->join('ad_privilege', 'ad_privilege.id', '=', 'ad_role_privilege.privilege_id')
            ->join('ad_resource', 'ad_resource.id', '=', 'ad_privilege.resource_id')->where('ad_resource.alias', 'job')
            ->distinct()->pluck('role_id')->all();
        $this->assertSame([2], $roles);
    }

    public function test_only_open_postings_are_listed_in_admin_order(): void
    {
        $this->travelTo('2026-11-17 10:00:00');
        $this->job(['slug' => 'b', 'title' => 'B', 'ord' => 2]);
        $this->job(['slug' => 'a', 'title' => 'A', 'ord' => 1, 'deadline' => '2026-11-17']); // last day: still open
        $this->job(['slug' => 'expired', 'deadline' => '2026-11-16']);
        $this->job(['slug' => 'closed', 'status' => 'closed']);
        $this->job(['slug' => 'draft', 'status' => 'draft', 'posted_at' => null]);

        $this->assertSame(['a', 'b'], array_column(CareerContentService::index()['items'], 'slug'));
        $json = $this->api('/careers')->assertOk()->json();
        $this->assertSame(['a', 'b'], array_column($json['items'], 'slug'));
        $this->assertSame('hrm@greenrubycruises.com', $json['email']);
    }

    public function test_detail_states_and_hidden_rows(): void
    {
        $this->travelTo('2026-11-17 10:00:00');
        $this->job(['slug' => 'open', 'apply_email' => 'sales@greenrubycruises.com', 'benefits' => '<p>&nbsp;</p>', 'employment_type' => 'full-time']);
        $this->job(['slug' => 'expired', 'deadline' => '2026-11-16']);
        $this->job(['slug' => 'closed', 'status' => 'closed']);
        $this->job(['slug' => 'draft', 'status' => 'draft', 'posted_at' => null]);
        $this->job(['slug' => 'never-published', 'status' => 'closed', 'posted_at' => null]);

        $open = $this->api('/careers/open')->assertOk()->json();
        $this->assertSame('open', $open['state']);
        $this->assertSame('sales@greenrubycruises.com', $open['applyEmail']);
        $this->assertNull($open['benefitsHtml'], 'an editor\'s empty paragraph is no section');
        $this->assertSame('<p>Mô tả</p>', $open['descriptionHtml']);
        $this->assertSame('full-time', $open['employmentType']);

        $this->assertSame('closed', $this->api('/careers/expired')->json('state'));
        $this->assertSame('closed', $this->api('/careers/closed')->json('state'));
        $this->api('/careers/draft')->assertNotFound();
        $this->api('/careers/never-published')->assertNotFound();
        $this->api('/careers/missing')->assertNotFound();
    }

    public function test_default_email_follows_the_system_config(): void
    {
        DB::table('ad_config')->where('key', 'recruitment-email')->update(['value' => 'tuyendung@greenrubycruises.com']);
        $this->assertSame('tuyendung@greenrubycruises.com', CareerContentService::defaultEmail());
        DB::table('ad_config')->where('key', 'recruitment-email')->update(['value' => 'not an email']);
        $this->assertSame(CareerContentService::DEFAULT_EMAIL, CareerContentService::defaultEmail());
    }

    public function test_admin_create_publish_close_reopen(): void
    {
        $this->travelTo('2026-11-17 10:00:00');
        $this->get('/admincp/job')->assertOk()->assertSee('Quản lý tuyển dụng');

        $this->post('/admincp/job/store', ['title' => 'Chuyên viên Marketing', 'department' => 'Marketing', 'status' => 'published', 'description' => '<p>x</p>'])
            ->assertRedirect();
        $job = DB::table('app_job')->first();
        $this->assertSame('chuyen-vien-marketing', $job->slug);
        $this->assertSame('2026-11-17', substr($job->posted_at, 0, 10), 'datePosted set on first publish');

        $this->post('/admincp/job/status', ['id' => $job->id, 'status' => 'closed'])->assertRedirect();
        $this->assertSame('closed', DB::table('app_job')->value('status'));
        $this->post('/admincp/job/status', ['id' => $job->id, 'status' => 'published'])->assertRedirect();
        $this->assertSame('published', DB::table('app_job')->value('status'));
    }

    public function test_admin_validation(): void
    {
        $this->travelTo('2026-11-17 10:00:00');
        $this->job(['slug' => 'sales-ota']);
        $this->post('/admincp/job/store', ['title' => 'Sales OTA', 'department' => 'X', 'status' => 'draft'])->assertSessionHasErrors('slug');
        $this->post('/admincp/job/store', ['title' => 'Y', 'department' => 'X', 'status' => 'published', 'description' => '<p>&nbsp;</p>'])->assertSessionHasErrors('description');
        $this->post('/admincp/job/store', ['title' => 'Z', 'department' => 'X', 'status' => 'published', 'description' => '<p>x</p>', 'deadline' => '2026-11-16'])->assertSessionHasErrors('deadline');
        $this->post('/admincp/job/store', ['title' => 'W', 'department' => 'X', 'status' => 'draft', 'apply_email' => 'nope'])->assertSessionHasErrors('apply_email');
        $this->assertSame(1, DB::table('app_job')->count());
    }

    public function test_users_without_the_job_privilege_are_refused(): void
    {
        $this->giveRoles(2, ['Biên dịch']);
        $this->actingAs(\Modules\BackEnd\Entities\AdUser::find(2), 'admin');
        $this->get('/admincp/job')->assertForbidden();
        $this->post('/admincp/job/store', ['title' => 'X', 'department' => 'X', 'status' => 'published', 'description' => '<p>x</p>'])->assertForbidden();
        $this->assertSame(0, DB::table('app_job')->count());
    }

    public function test_admin_saves_revalidate_the_careers_pages(): void
    {
        $route = Route::getRoutes()->getByName('backend.job.update');
        $request = Request::create('/admincp/job/update/1', 'POST');
        $request->setRouteResolver(fn () => $route->bind($request));
        $this->assertSame(['careers'], (new RevalidateFrontend())->tagsFor($request));
        $this->assertTrue((new RevalidateFrontend())->expires($request), 'job saves expire the cache at once');
        $other = Request::create('/admincp/destination/update/1', 'POST');
        $other->setRouteResolver(fn () => Route::getRoutes()->getByName('backend.destination.update')->bind($other));
        $this->assertFalse((new RevalidateFrontend())->expires($other), 'other entities keep stale-while-revalidate');
    }

    public function test_sitemap_lists_hub_and_open_postings_only(): void
    {
        $this->travelTo('2026-11-17 10:00:00');
        $this->job(['slug' => 'open']);
        $this->job(['slug' => 'expired', 'deadline' => '2026-11-16']);
        $this->job(['slug' => 'closed', 'status' => 'closed']);
        $this->job(['slug' => 'draft', 'status' => 'draft']);

        $xml = $this->get('/sitemap-career.xml')->assertOk()->getContent();
        preg_match_all('#<loc>([^<]+)</loc>#', $xml, $m);
        $paths = array_map(fn ($u) => parse_url($u, PHP_URL_PATH), $m[1]);
        $this->assertSame(['/tuyen-dung', '/tuyen-dung/open'], $paths);
    }
}
