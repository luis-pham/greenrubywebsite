<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BookingPrivilegeSeeder extends Seeder
{
    /**
     * Thêm quyền booking-manager và gán cho tất cả vai trò hiện có.
     */
    public function run(): void
    {
        $resourceAlias = 'booking-manager';
        $resource = DB::table('ad_resource')->where('alias', $resourceAlias)->first();

        if (!$resource) {
            $maxOrd = (int) DB::table('ad_resource')->max('ord');
            $resourceId = DB::table('ad_resource')->insertGetId([
                'name' => 'Booking & Yêu cầu',
                'alias' => $resourceAlias,
                'ord' => $maxOrd + 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $resourceId = $resource->id;
        }

        $privileges = [
            ['name' => 'Xem', 'alias' => 'read', 'ord' => 1],
            ['name' => 'Cập nhật', 'alias' => 'update', 'ord' => 2],
            ['name' => 'Xóa', 'alias' => 'delete', 'ord' => 3],
        ];

        $privilegeIds = [];
        foreach ($privileges as $privilege) {
            $existing = DB::table('ad_privilege')
                ->where('resource_id', $resourceId)
                ->where('alias', $privilege['alias'])
                ->first();

            if ($existing) {
                $privilegeIds[] = $existing->id;
                continue;
            }

            $privilegeIds[] = DB::table('ad_privilege')->insertGetId([
                'resource_id' => $resourceId,
                'name' => $privilege['name'],
                'alias' => $privilege['alias'],
                'ord' => $privilege['ord'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $readPrivilegeId = DB::table('ad_privilege')
            ->where('resource_id', $resourceId)
            ->where('alias', 'read')
            ->value('id');

        if ($readPrivilegeId) {
            DB::table('ad_menu')
                ->where('url', '/admincp/booking-manager')
                ->update(['privilege_id' => $readPrivilegeId]);
        }

        $roleIds = DB::table('ad_role')->pluck('id');
        foreach ($roleIds as $roleId) {
            foreach ($privilegeIds as $privilegeId) {
                $exists = DB::table('ad_role_privilege')
                    ->where('role_id', $roleId)
                    ->where('privilege_id', $privilegeId)
                    ->exists();

                if (!$exists) {
                    DB::table('ad_role_privilege')->insert([
                        'role_id' => $roleId,
                        'privilege_id' => $privilegeId,
                    ]);
                }
            }
        }

        $this->command?->info('Đã thêm quyền booking-manager.');
    }
}
