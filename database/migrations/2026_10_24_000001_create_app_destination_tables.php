<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Destinations (caves, islands, villages, lagoons of Ha Long Bay and Lan Ha Bay) shown on the Next.js
 * /destinations hub and detail pages. One row per language, paired through content_translations
 * (entity_type "destination"); planned Green Ruby stops come from app_destination_itinerary.
 *
 * Also adds the admin resource/privileges/menu, and app_page rows (SEO card only) for the destinations hub
 * and the two guide pages, which exist only on the Next.js frontend.
 */
return new class extends Migration
{
    private const PAGE_CODES = [
        'destinations' => 'Điểm đến (hub)',
        'guide-first-time' => 'Cẩm nang: Lần đầu đi du thuyền',
        'guide-transfer' => 'Cẩm nang: Hà Nội đi Hạ Long',
    ];

    public function up(): void
    {
        Schema::create('app_destination', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('language_id');
            $table->string('slug', 120);
            $table->string('name', 150);
            $table->string('bay', 20); // ha-long | lan-ha
            $table->string('category', 30); // see config('backend.destinationCategory')
            $table->text('summary')->nullable();
            $table->longText('content')->nullable();
            $table->json('facts')->nullable(); // [{label, value}]
            $table->json('faq')->nullable(); // [{q, a}]
            $table->json('sources')->nullable(); // [{title, url}]
            $table->decimal('latitude', 9, 6)->nullable();
            $table->decimal('longitude', 9, 6)->nullable();
            $table->boolean('coords_verified')->default(false);
            $table->string('coords_source', 255)->nullable();
            $table->string('image_link', 255)->nullable();
            $table->string('image_alt', 255)->nullable();
            $table->string('image_credit', 255)->nullable();
            $table->string('status', 20)->default('draft'); // draft | review | published
            $table->text('editor_notes')->nullable(); // internal, never sent to the frontend
            $table->date('fact_checked_at')->nullable();
            $table->string('seo_title', 65)->nullable();
            $table->string('seo_description', 255)->nullable();
            $table->unsignedInteger('ord')->default(0);
            $table->dateTime('created_at')->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->unsignedInteger('updated_by')->nullable();
            $table->unique(['language_id', 'slug']);
            $table->index(['language_id', 'status']);
        });

        Schema::create('app_destination_itinerary', function (Blueprint $table) {
            $table->unsignedInteger('destination_id');
            $table->unsignedInteger('itinerary_id');
            $table->primary(['destination_id', 'itinerary_id']);
            $table->index('itinerary_id');
        });

        $this->addPermissions();
        $this->addPages();
    }

    public function down(): void
    {
        $resourceId = DB::table('ad_resource')->where('alias', 'destination')->value('id');
        if ($resourceId) {
            $privilegeIds = DB::table('ad_privilege')->where('resource_id', $resourceId)->pluck('id');
            DB::table('ad_role_privilege')->whereIn('privilege_id', $privilegeIds)->delete();
            DB::table('ad_privilege')->where('resource_id', $resourceId)->delete();
            DB::table('ad_resource')->where('id', $resourceId)->delete();
        }
        DB::table('ad_menu')->where('url', '/admincp/destination')->delete();
        foreach (array_keys(self::PAGE_CODES) as $code) {
            DB::table('ad_menu')->where('url', '/admincp/page-config/' . $code)->delete();
            DB::table('app_page')->where('code', $code)->delete();
        }
        DB::table('content_translations')->where('entity_type', 'destination')->delete();
        Schema::dropIfExists('app_destination_itinerary');
        Schema::dropIfExists('app_destination');
    }

    /** Resource "destination" with the usual privileges, granted to every role that may edit experiences. */
    private function addPermissions(): void
    {
        if (DB::table('ad_resource')->where('alias', 'destination')->exists()) {
            return;
        }
        $resourceId = DB::table('ad_resource')->insertGetId([
            'name' => 'Quản lý điểm đến',
            'alias' => 'destination',
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

        $experience = DB::table('ad_resource')->where('alias', 'group-exp-activity')->value('id');
        $roles = $experience
            ? DB::table('ad_role_privilege')
                ->join('ad_privilege', 'ad_privilege.id', '=', 'ad_role_privilege.privilege_id')
                ->where('ad_privilege.resource_id', $experience)
                ->distinct()
                ->pluck('ad_role_privilege.role_id')
            : collect();
        foreach ($roles as $roleId) {
            foreach ($privileges as $privilegeId) {
                DB::table('ad_role_privilege')->insertOrIgnore(['role_id' => $roleId, 'privilege_id' => $privilegeId]);
            }
        }

        $experienceMenu = DB::table('ad_menu')->where('url', '/admincp/exp-activity')->first();
        if ($experienceMenu && !DB::table('ad_menu')->where('url', '/admincp/destination')->exists()) {
            DB::table('ad_menu')->insert([
                'parent_id' => $experienceMenu->parent_id,
                'privilege_id' => $privileges['read'],
                'name' => 'Điểm đến',
                'url' => '/admincp/destination',
                'is_multi_language' => 1,
                'icon' => 'fas fa-map-marked-alt',
                'ord' => (int) $experienceMenu->ord + 1,
                'status' => 1,
                'created_at' => now(),
                'created_by' => 1,
            ]);
        }
    }

    /** SEO cards in "Quản lý cấu hình trang"; empty SEO = the frontend keeps its own title/description. */
    private function addPages(): void
    {
        $eco = DB::table('ad_menu')->where('url', '/admincp/page-config/eco-dashboard')->first();
        $ord = $eco ? (int) $eco->ord : 7;
        foreach (self::PAGE_CODES as $code => $title) {
            foreach (DB::table('ad_language')->get() as $language) {
                if (!DB::table('app_page')->where('code', $code)->where('language_id', $language->id)->exists()) {
                    DB::table('app_page')->insert([
                        'language_id' => $language->id,
                        'code' => $code,
                        'title' => $title,
                        'created_at' => now(),
                        'created_by' => 1,
                    ]);
                }
            }
            if ($eco && !DB::table('ad_menu')->where('url', '/admincp/page-config/' . $code)->exists()) {
                DB::table('ad_menu')->insert([
                    'parent_id' => $eco->parent_id,
                    'privilege_id' => $eco->privilege_id,
                    'name' => $title,
                    'url' => '/admincp/page-config/' . $code,
                    'is_multi_language' => 1,
                    'icon' => 'fas fa-compass',
                    'ord' => ++$ord,
                    'status' => 1,
                    'created_at' => now(),
                    'created_by' => 1,
                ]);
            }
        }
    }
};
