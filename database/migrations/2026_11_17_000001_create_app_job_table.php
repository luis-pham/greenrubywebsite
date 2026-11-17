<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Job postings (recruitment) shown on the Next.js /tuyen-dung pages. Vietnamese only, so the table has no
 * language_id and no translation pairing. Only "published" rows whose deadline has not passed are listed;
 * "closed" (or expired) rows keep their URL with a closed notice; drafts are never public.
 *
 * Also adds the admin resource "job" (privileges read/create/update/delete, granted to the roles that may edit
 * articles), the admin menu "Tuyển dụng" under "Quản lý khác", and the system config "recruitment-email"
 * (default application address, editable in "Cấu hình hệ thống").
 */
return new class extends Migration
{
    private const DEFAULT_EMAIL = 'hrm@greenrubycruises.com';

    public function up(): void
    {
        if (!Schema::hasTable('app_job')) {
            Schema::create('app_job', function (Blueprint $table) {
                $table->increments('id');
                $table->string('slug', 120)->unique();
                $table->string('title', 150);
                $table->string('department', 100);
                $table->string('employment_type', 20)->nullable(); // see config('backend.jobEmploymentType')
                $table->string('location', 150)->nullable(); // shown on the page, e.g. "Tuần Châu, Hạ Long, Quảng Ninh"
                $table->string('address_locality', 100)->nullable(); // JobPosting jobLocation (city), e.g. "Hạ Long"
                $table->string('address_region', 100)->nullable(); // JobPosting jobLocation (province), e.g. "Quảng Ninh"
                $table->text('summary')->nullable(); // overview under the H1, meta description fallback
                $table->longText('intro')->nullable(); // Giới thiệu công việc
                $table->longText('description')->nullable(); // Mô tả công việc
                $table->longText('requirements')->nullable(); // Yêu cầu công việc
                $table->longText('benefits')->nullable(); // Quyền lợi
                $table->string('apply_email', 150)->nullable(); // empty = config "recruitment-email"
                $table->string('status', 20)->default('draft'); // draft | published | closed
                $table->date('posted_at')->nullable(); // JobPosting datePosted; set on first publish when empty
                $table->date('deadline')->nullable(); // last day to apply (inclusive, Vietnam time)
                $table->string('seo_title', 65)->nullable();
                $table->string('seo_description', 255)->nullable();
                $table->unsignedInteger('ord')->default(0);
                $table->dateTime('created_at')->nullable();
                $table->unsignedInteger('created_by')->nullable();
                $table->dateTime('updated_at')->nullable();
                $table->unsignedInteger('updated_by')->nullable();
                $table->index(['status', 'ord']);
            });
        }

        $this->addPermissions();
        $this->addConfig();
    }

    public function down(): void
    {
        $resourceId = DB::table('ad_resource')->where('alias', 'job')->value('id');
        if ($resourceId) {
            $privilegeIds = DB::table('ad_privilege')->where('resource_id', $resourceId)->pluck('id');
            DB::table('ad_role_privilege')->whereIn('privilege_id', $privilegeIds)->delete();
            DB::table('ad_privilege')->where('resource_id', $resourceId)->delete();
            DB::table('ad_resource')->where('id', $resourceId)->delete();
        }
        DB::table('ad_menu')->where('url', '/admincp/job')->delete();
        DB::table('ad_config')->where('key', 'recruitment-email')->delete();
        Schema::dropIfExists('app_job');
    }

    /** Resource "job" with the usual privileges, granted to every role that may edit articles. */
    private function addPermissions(): void
    {
        if (DB::table('ad_resource')->where('alias', 'job')->exists()) {
            return;
        }
        $resourceId = DB::table('ad_resource')->insertGetId([
            'name' => 'Quản lý tuyển dụng',
            'alias' => 'job',
            'ord' => (int) DB::table('ad_resource')->max('ord') + 1,
            'created_at' => now(),
            'created_by' => 1,
        ]);

        $privileges = [];
        foreach ([['Xem', 'read'], ['Thêm', 'create'], ['Sửa', 'update'], ['Xóa', 'delete']] as $i => [$name, $alias]) {
            $privileges[$alias] = DB::table('ad_privilege')->insertGetId([
                'resource_id' => $resourceId,
                'name' => $name,
                'alias' => $alias,
                'ord' => $i + 1,
                'created_at' => now(),
                'created_by' => 1,
            ]);
        }

        $article = DB::table('ad_resource')->where('alias', 'article')->value('id');
        $roles = $article
            ? DB::table('ad_role_privilege')
                ->join('ad_privilege', 'ad_privilege.id', '=', 'ad_role_privilege.privilege_id')
                ->where('ad_privilege.resource_id', $article)
                ->where('ad_privilege.alias', 'update')
                ->distinct()
                ->pluck('ad_role_privilege.role_id')
            : collect();
        foreach ($roles as $roleId) {
            foreach ($privileges as $privilegeId) {
                DB::table('ad_role_privilege')->insertOrIgnore(['role_id' => $roleId, 'privilege_id' => $privilegeId]);
            }
        }

        // "Quản lý khác" (testimonials, front-end menu); falls back to a top-level item.
        $other = DB::table('ad_menu')->whereNull('parent_id')->where('name', 'Quản lý khác')->first();
        if (!DB::table('ad_menu')->where('url', '/admincp/job')->exists()) {
            DB::table('ad_menu')->insert([
                'parent_id' => $other->id ?? null,
                'privilege_id' => $privileges['read'],
                'name' => 'Tuyển dụng',
                'url' => '/admincp/job',
                'is_multi_language' => 0,
                'icon' => 'fas fa-briefcase',
                'ord' => $other ? (int) DB::table('ad_menu')->where('parent_id', $other->id)->max('ord') + 1 : (int) DB::table('ad_menu')->max('ord') + 1,
                'status' => 1,
                'created_at' => now(),
                'created_by' => 1,
            ]);
        }
    }

    /** Default application e-mail (common to all languages), next to the site e-mail in "Cấu hình hệ thống". */
    private function addConfig(): void
    {
        if (DB::table('ad_config')->where('key', 'recruitment-email')->exists()) {
            return;
        }
        $email = DB::table('ad_config')->where('key', 'email')->first();
        DB::table('ad_config')->insert([
            'language_id' => null,
            'label' => 'Email tuyển dụng',
            'key' => 'recruitment-email',
            'value' => self::DEFAULT_EMAIL,
            'type' => $email->type ?? 1,
            'ord' => $email->ord ?? 6,
            'updated_at' => now(),
            'updated_by' => 1,
        ]);
    }
};
