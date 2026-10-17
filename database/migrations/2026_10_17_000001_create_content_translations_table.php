<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Explicit EN <-> VI pairing of content rows (each language is its own row with its own id).
        // Rows sharing (entity_type, group_key) are translations of each other; see App\Services\Content\ContentTranslations.
        Schema::create('content_translations', function (Blueprint $table) {
            $table->id();
            $table->string('entity_type', 32);
            $table->unsignedInteger('entity_id');
            $table->char('group_key', 36);
            $table->unsignedInteger('language_id');
            $table->timestamps();
            $table->unique(['entity_type', 'entity_id']);
            $table->unique(['entity_type', 'group_key', 'language_id']);
        });

        // Admin screen "Liên kết bản dịch" under "Quản lý hệ thống", guarded by config-update.
        $parentId = DB::table('ad_menu')->whereNull('parent_id')->where('name', 'Quản lý hệ thống')->value('id');
        $privilegeId = DB::table('ad_privilege')
            ->join('ad_resource', 'ad_resource.id', '=', 'ad_privilege.resource_id')
            ->where('ad_resource.alias', 'config')
            ->where('ad_privilege.alias', 'update')
            ->value('ad_privilege.id');
        if ($parentId && !DB::table('ad_menu')->where('url', '/admincp/translation')->exists()) {
            DB::table('ad_menu')->insert([
                'parent_id' => $parentId,
                'name' => 'Liên kết bản dịch',
                'url' => '/admincp/translation',
                'ord' => 7,
                'privilege_id' => $privilegeId,
                'is_multi_language' => 0,
                'status' => 1,
                'icon' => 'fas fa-language',
                'created_at' => now(),
                'created_by' => 1,
            ]);
        }
    }

    public function down(): void
    {
        DB::table('ad_menu')->where('url', '/admincp/translation')->delete();
        Schema::dropIfExists('content_translations');
    }
};
