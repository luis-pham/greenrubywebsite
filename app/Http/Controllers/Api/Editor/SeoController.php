<?php

namespace App\Http\Controllers\Api\Editor;

use App\Services\Seo\KeywordMatcher;
use App\Services\Seo\SeoGuard;
use App\Services\Seo\SeoInventory;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Modules\BackEnd\Entities\AppArticle;

/**
 * SEO governance for the writing app (docs/seo-audit/14):
 *   GET  /seo/check        cannibalization report for an article, a plan item or a typed keyword (G2)
 *   GET  /seo/owners       keyword ownership registry of a language (G1); writes need an override role
 *   GET  /seo/log          guard decisions and overrides (G3 audit trail)
 *   POST /plan/{id}/override  record an override with a reason for a blocked plan item
 */
class SeoController extends Controller
{
    private const LANGS = ['vi' => 1, 'en' => 2];

    public function check(Request $request)
    {
        $in = $request->validate([
            'articleId' => 'nullable|integer',
            'planId' => 'nullable|integer',
            'lang' => 'nullable|in:vi,en',
            'keyword' => 'nullable|string|max:255',
            'intent' => 'nullable|in:I,C,T,N',
            'title' => 'nullable|string|max:255',
        ]);
        if (!empty($in['articleId'])) {
            $candidate = SeoGuard::candidateFromArticle(AppArticle::findOrFail($in['articleId']));
            // The editor sends what is typed now, so the check does not wait for a save.
            foreach (['keyword', 'title'] as $field) {
                if (!empty($in[$field])) {
                    $candidate[$field] = trim($in[$field]);
                }
            }
        } elseif (!empty($in['planId'])) {
            $candidate = SeoGuard::candidateFromPlan(DB::table('app_article_plan')->where('id', $in['planId'])->first() ?? abort(404));
        } else {
            if (empty($in['keyword']) || empty($in['lang'])) {
                throw ValidationException::withMessages(['keyword' => 'Cần từ khóa và ngôn ngữ.']);
            }
            $candidate = ['language_id' => self::LANGS[$in['lang']], 'keyword' => $in['keyword'], 'secondary' => [],
                'intent' => $in['intent'] ?? null, 'title' => $in['title'] ?? '', 'content' => '', 'self' => []];
        }

        return response()->json(['report' => SeoGuard::check($candidate), 'canOverride' => SeoGuard::canOverride(Auth::guard('admin')->id())]);
    }

    public function owners(Request $request)
    {
        $lang = $request->input('lang', 'en');
        $rows = DB::table('app_seo_owner')->where('language_id', self::LANGS[$lang] ?? 2)->orderBy('cluster')->orderBy('keyword')->get();

        return response()->json(['items' => $rows->map(fn ($o) => self::owner($o))->values()]);
    }

    public function storeOwner(Request $request)
    {
        $this->authorizeGovernance();
        $data = $this->validateOwner($request);
        $id = DB::table('app_seo_owner')->insertGetId($data + ['created_at' => now(), 'created_by' => Auth::guard('admin')->id()]);

        return response()->json(['item' => self::owner(DB::table('app_seo_owner')->find($id))], 201);
    }

    public function updateOwner(Request $request, int $id)
    {
        $this->authorizeGovernance();
        abort_if(!DB::table('app_seo_owner')->where('id', $id)->exists(), 404);
        $data = $this->validateOwner($request, $id);
        DB::table('app_seo_owner')->where('id', $id)->update($data + ['updated_at' => now(), 'updated_by' => Auth::guard('admin')->id()]);

        return response()->json(['item' => self::owner(DB::table('app_seo_owner')->find($id))]);
    }

    public function log(Request $request)
    {
        $q = DB::table('app_seo_guard_log')->orderByDesc('id');
        if ($request->filled('entityType')) {
            $q->where('entity_type', $request->input('entityType'))->where('entity_id', (int) $request->input('entityId'));
        }
        $users = DB::table('ad_user')->pluck('username', 'id');

        return response()->json(['items' => $q->limit(100)->get()->map(fn ($l) => [
            'id' => (int) $l->id, 'action' => $l->action, 'entityType' => $l->entity_type, 'entityId' => (int) $l->entity_id,
            'keyword' => $l->keyword, 'verdict' => $l->verdict, 'recommendation' => $l->recommendation, 'outcome' => $l->outcome,
            'reason' => $l->override_reason, 'user' => $users[$l->user_id] ?? null, 'at' => ArticleController::date($l->created_at),
            'report' => json_decode($l->report, true),
        ])->values()]);
    }

    public function overridePlan(Request $request, int $id)
    {
        $in = $request->validate(['reason' => 'required|string|min:10|max:2000']);
        $plan = DB::table('app_article_plan')->where('id', $id)->first();
        abort_if(!$plan, 404);
        $userId = Auth::guard('admin')->id();
        if (!SeoGuard::canOverride($userId)) {
            return response()->json(['message' => 'Chỉ quản trị viên được override cảnh báo trùng nội dung.'], 403);
        }
        $gate = SeoGuard::gate('override', 'plan', $id, SeoGuard::candidateFromPlan($plan), $userId, $in['reason']);

        return response()->json(['outcome' => $gate['outcome'], 'report' => $gate['report']]);
    }

    private function authorizeGovernance(): void
    {
        abort_unless(SeoGuard::canOverride(Auth::guard('admin')->id()), 403, 'Chỉ quản trị viên được sửa bảng sở hữu từ khóa.');
    }

    private function validateOwner(Request $request, ?int $id = null): array
    {
        $in = $request->validate([
            'lang' => 'required|in:vi,en',
            'keyword' => 'required|string|max:191',
            'intent' => 'nullable|in:I,C,T,N',
            'ownerUrl' => ['nullable', 'string', 'max:255', 'regex:#^/#'],
            'ownerType' => 'nullable|string|max:20',
            'pageRole' => 'nullable|in:hub,pillar,detail',
            'cluster' => 'nullable|string|max:120',
            'pillar' => 'nullable|string|max:120',
            'supportingKeywords' => 'nullable|array',
            'supportingKeywords.*' => 'string|max:191',
            'status' => ['required', Rule::in(['active', 'gap', 'retired'])],
            'note' => 'nullable|string|max:4000',
        ]);
        $languageId = self::LANGS[$in['lang']];
        $key = KeywordMatcher::normalize($in['keyword']);
        $duplicate = DB::table('app_seo_owner')->where('language_id', $languageId)->where('keyword_key', $key)
            ->where('intent', $in['intent'] ?? null)->when($id, fn ($q) => $q->where('id', '!=', $id))->first();
        if ($duplicate) {
            throw ValidationException::withMessages(['keyword' => "Từ khóa này (cùng intent) đã thuộc {$duplicate->owner_url}."]);
        }
        if ($in['status'] === 'active' && empty($in['ownerUrl'])) {
            throw ValidationException::withMessages(['ownerUrl' => 'Từ khóa đang dùng cần URL sở hữu (hoặc đặt trạng thái "gap").']);
        }

        return [
            'language_id' => $languageId,
            'keyword' => trim($in['keyword']),
            'keyword_key' => $key,
            'intent' => $in['intent'] ?? null,
            'owner_url' => $in['ownerUrl'] ?? null,
            'owner_type' => $in['ownerType'] ?? null,
            'page_role' => $in['pageRole'] ?? null,
            'cluster' => $in['cluster'] ?? null,
            'pillar' => $in['pillar'] ?? null,
            'supporting_keywords' => json_encode(array_values($in['supportingKeywords'] ?? []), JSON_UNESCAPED_UNICODE),
            'status' => $in['status'],
            'note' => $in['note'] ?? null,
        ];
    }

    public static function owner(object $o): array
    {
        return [
            'id' => (int) $o->id, 'lang' => (int) $o->language_id === 1 ? 'vi' : 'en', 'keyword' => $o->keyword, 'intent' => $o->intent,
            'ownerUrl' => $o->owner_url, 'ownerType' => $o->owner_type, 'pageRole' => $o->page_role, 'cluster' => $o->cluster,
            'pillar' => $o->pillar, 'supportingKeywords' => SeoInventory::list($o->supporting_keywords), 'status' => $o->status, 'note' => $o->note,
        ];
    }
}
