<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 1E final hardening.
 *   - Editorial privileges (existing model: ad_resource / ad_privilege, gates "<resource>-<privilege>") and roles that
 *     bundle them. Nothing is assigned to any user or existing role: no account gains a privilege silently; an
 *     administrator assigns the roles in the user form (several roles per user are allowed). The super
 *     administrator (backend.adUserAdmin) keeps every ability, as before.
 *   - editorial_audit_log: actor, action and time of every workflow step, slug change and locale status change.
 *   - content_revision: last editor (concurrent-edit lock) and discard (rejected revisions keep their history).
 */
return new class extends Migration
{
    /** resource alias => [name, [privilege alias => name]] */
    public const RESOURCES = [
        'translation' => ['Bản dịch (ngôn ngữ mới)', [
            'edit' => 'Soạn bản dịch', 'approve' => 'Duyệt bản dịch', 'seo-approve' => 'Duyệt SEO bản dịch',
            'legal-approve' => 'Duyệt pháp lý bản dịch', 'publish' => 'Xuất bản / gỡ bản dịch',
        ]],
        'locale' => ['Ngôn ngữ website', ['update' => 'Đổi trạng thái ngôn ngữ']],
        'slug' => ['URL (slug)', ['update' => 'Đổi slug']],
    ];

    /** role name => [resource-privilege, …] */
    public const ROLES = [
        'Biên dịch' => ['translation-edit'],
        'Duyệt bản dịch' => ['translation-approve'],
        'Duyệt SEO' => ['translation-seo-approve', 'slug-update'],
        'Duyệt pháp lý' => ['translation-legal-approve'],
        'Xuất bản bản dịch' => ['translation-publish'],
        'Quản lý ngôn ngữ' => ['locale-update'],
    ];

    public function up(): void
    {
        $now = now();
        DB::transaction(function () use ($now) {
            $ord = (int) DB::table('ad_resource')->max('ord');
            foreach (self::RESOURCES as $alias => [$name, $privileges]) {
                $resourceId = DB::table('ad_resource')->where('alias', $alias)->value('id')
                    ?? DB::table('ad_resource')->insertGetId(['name' => $name, 'alias' => $alias, 'ord' => ++$ord, 'created_at' => $now, 'created_by' => 1]);
                $i = 0;
                foreach ($privileges as $privilege => $privilegeName) {
                    if (!DB::table('ad_privilege')->where('resource_id', $resourceId)->where('alias', $privilege)->exists()) {
                        DB::table('ad_privilege')->insert(['resource_id' => $resourceId, 'name' => $privilegeName, 'alias' => $privilege, 'ord' => ++$i, 'created_at' => $now, 'created_by' => 1]);
                    }
                }
            }
            foreach (self::ROLES as $role => $abilities) {
                $roleId = DB::table('ad_role')->where('name', $role)->value('id')
                    ?? DB::table('ad_role')->insertGetId(['name' => $role, 'created_at' => $now, 'created_by' => 1]);
                foreach ($abilities as $ability) {
                    [$resource, $privilege] = explode('-', $ability, 2);
                    $privilegeId = DB::table('ad_privilege')->join('ad_resource', 'ad_resource.id', '=', 'ad_privilege.resource_id')
                        ->where('ad_resource.alias', $resource)->where('ad_privilege.alias', $privilege)->value('ad_privilege.id');
                    if ($privilegeId && !DB::table('ad_role_privilege')->where('role_id', $roleId)->where('privilege_id', $privilegeId)->exists()) {
                        DB::table('ad_role_privilege')->insert(['role_id' => $roleId, 'privilege_id' => $privilegeId]);
                    }
                }
            }
        });

        Schema::create('editorial_audit_log', function (Blueprint $t) {
            $t->bigIncrements('id');
            $t->string('entity_type', 32);
            $t->unsignedInteger('entity_id');
            $t->string('action', 32);
            $t->string('from_state', 32)->nullable();
            $t->string('to_state', 32)->nullable();
            $t->unsignedBigInteger('revision_id')->nullable();
            $t->unsignedInteger('actor')->nullable();
            $t->string('detail', 255)->nullable();
            $t->dateTime('created_at');
            $t->index(['entity_type', 'entity_id']);
        });
        // Local clones migrated before these columns joined 2026_11_04_000001 (never deployed) get them here.
        if (Schema::hasTable('content_revision') && !Schema::hasColumn('content_revision', 'discarded_at')) {
            Schema::table('content_revision', function (Blueprint $t) {
                $t->unsignedInteger('updated_by')->nullable()->after('created_by');
                $t->dateTime('discarded_at')->nullable()->after('applied_by');
                $t->unsignedInteger('discarded_by')->nullable()->after('discarded_at');
            });
        }
    }

    public function down(): void
    {
        // Audit history and role assignments are records of people's work: refuse to destroy them.
        if (Schema::hasTable('editorial_audit_log') && DB::table('editorial_audit_log')->exists()) {
            throw new RuntimeException('Refusing to drop editorial_audit_log: it has rows (audit history).');
        }
        $roleIds = DB::table('ad_role')->whereIn('name', array_keys(self::ROLES))->pluck('id');
        if (DB::table('ad_user_role')->whereIn('role_id', $roleIds)->exists()) {
            throw new RuntimeException('Refusing to remove the editorial roles: users are assigned to them.');
        }
        Schema::dropIfExists('editorial_audit_log');
        DB::transaction(function () use ($roleIds) {
            $resourceIds = DB::table('ad_resource')->whereIn('alias', array_keys(self::RESOURCES))->pluck('id');
            $privilegeIds = DB::table('ad_privilege')->whereIn('resource_id', $resourceIds)->pluck('id');
            DB::table('ad_role_privilege')->whereIn('privilege_id', $privilegeIds)->delete();
            DB::table('ad_role')->whereIn('id', $roleIds)->delete();
            DB::table('ad_privilege')->whereIn('id', $privilegeIds)->delete();
            DB::table('ad_resource')->whereIn('id', $resourceIds)->delete();
        });
    }
};
