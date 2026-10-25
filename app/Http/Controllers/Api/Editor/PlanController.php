<?php

namespace App\Http\Controllers\Api\Editor;

use App\Services\Seo\SeoGuard;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\BackEnd\Entities\AppArticle;
use Modules\BackEnd\Services\AppArticleService;

/** The content plan (pillar/cluster articles to write), imported from the plan JSON. */
class PlanController extends Controller
{
    public function index()
    {
        $rows = DB::table('app_article_plan')->orderBy('id')->get();
        $articles = AppArticle::whereIn('id', $rows->pluck('article_id')->filter())->get(['id', 'title', 'is_published', 'publish_date'])->keyBy('id');

        return response()->json(['items' => $rows->map(function ($p) use ($articles) {
            $item = self::item($p);
            $a = $p->article_id ? $articles->get($p->article_id) : null;
            $item['article'] = $a ? ['id' => (int) $a->id, 'title' => $a->title, 'status' => ArticleController::status($a)] : null;

            return $item;
        })->all()]);
    }

    /** Upserts by code. Rows already linked to an article keep that link and their status. */
    public function import(Request $request)
    {
        $request->validate([
            'items' => 'required|array|max:500',
            'items.*.code' => 'required|string|max:16',
            'items.*.pillar' => 'required|string|max:8',
            'items.*.title' => 'required|string|max:255',
        ]);
        $items = $request->input('items');

        $created = 0;
        $updated = 0;
        DB::transaction(function () use ($items, &$created, &$updated) {
            foreach ($items as $i) {
                $row = [
                    'pillar' => $i['pillar'],
                    'pillar_name' => $i['pillarName'] ?? null,
                    'kind' => ($i['kind'] ?? 'cluster') === 'pillar' ? 'pillar' : 'cluster',
                    'lang' => ($i['lang'] ?? 'en') === 'vi' ? 'vi' : 'en',
                    'category_slug' => $i['categorySlug'] ?? null,
                    'title' => $i['title'],
                    'primary_kw' => $i['primaryKw'] ?? null,
                    'secondary_kw' => $i['secondaryKw'] ?? null,
                    'intent' => isset($i['intent']) ? substr($i['intent'], 0, 1) : null,
                    'words' => isset($i['words']) ? (int) $i['words'] : null,
                    'priority' => isset($i['priority']) ? max(1, min(3, (int) $i['priority'])) : 2,
                    'target_month' => $i['targetMonth'] ?? null,
                    'money_links' => json_encode($i['moneyLinks'] ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'siblings' => json_encode($i['siblings'] ?? [], JSON_UNESCAPED_UNICODE),
                    'note' => $i['note'] ?? null,
                    'evidence' => in_array($i['evidence'] ?? null, ArticleController::EVIDENCE, true) ? $i['evidence'] : null,
                    'updated_at' => now(),
                ];
                $status = in_array($i['status'] ?? '', ['wait', 'skip'], true) ? $i['status'] : 'idea';
                $existing = DB::table('app_article_plan')->where('code', $i['code'])->first();
                if ($existing) {
                    // A new plan version may defer or merge items; never override a status set by hand or an item already being written.
                    if (!$existing->article_id && $existing->status === 'idea') {
                        $row['status'] = $status;
                    }
                    DB::table('app_article_plan')->where('id', $existing->id)->update($row);
                    $updated++;
                } else {
                    $articleId = isset($i['articleId']) && AppArticle::where('id', $i['articleId'])->exists() ? (int) $i['articleId'] : null;
                    DB::table('app_article_plan')->insert($row + [
                        'code' => $i['code'],
                        'status' => $status,
                        'article_id' => $articleId,
                        'created_at' => now(),
                    ]);
                    $created++;
                }
            }
        });

        return response()->json(['created' => $created, 'updated' => $updated]);
    }

    public function update(Request $request, int $id)
    {
        $in = $request->validate([
            'status' => 'sometimes|in:idea,wait,skip',
            'note' => 'sometimes|nullable|string|max:4000',
            'targetMonth' => 'sometimes|nullable|string|max:40',
            'priority' => 'sometimes|integer|min:1|max:3',
            'articleId' => 'sometimes|nullable|integer',
        ]);
        $plan = DB::table('app_article_plan')->where('id', $id)->first();
        abort_if(!$plan, 404);

        $row = ['updated_at' => now()];
        foreach (['status' => 'status', 'note' => 'note', 'targetMonth' => 'target_month', 'priority' => 'priority'] as $k => $col) {
            if (array_key_exists($k, $in)) {
                $row[$col] = $in[$k];
            }
        }
        if (array_key_exists('articleId', $in)) {
            if ($in['articleId'] && !AppArticle::where('id', $in['articleId'])->exists()) {
                throw ValidationException::withMessages(['articleId' => 'Không tìm thấy bài viết.']);
            }
            if ($in['articleId']) {
                DB::table('app_article_plan')->where('article_id', $in['articleId'])->where('id', '!=', $id)->update(['article_id' => null]);
            }
            $row['article_id'] = $in['articleId'] ?: null;
        }
        DB::table('app_article_plan')->where('id', $id)->update($row);

        return response()->json(['item' => self::item(DB::table('app_article_plan')->where('id', $id)->first())]);
    }

    /**
     * Queues plan items for AI drafting (articles:ai-draft-run). {ids: [..]} or {} for every item that is
     * still an idea without an article. Items being written by hand are never queued.
     */
    public function aiQueue(Request $request)
    {
        $ids = array_map('intval', (array) $request->input('ids', []));
        $q = DB::table('app_article_plan')->where('status', 'idea')->whereNull('article_id')
            ->where(fn ($w) => $w->whereNull('ai_status')->orWhereIn('ai_status', ['error']));
        if ($ids) {
            $q->whereIn('id', $ids);
        }
        // Pre-writing guard: an item whose keyword is already owned elsewhere is not drafted (unless overridden).
        $allowed = [];
        $blocked = [];
        foreach ($q->get() as $plan) {
            $gate = SeoGuard::gate('ai_queue', 'plan', (int) $plan->id, SeoGuard::candidateFromPlan($plan), Auth::guard('admin')->id());
            if ($gate['allowed']) {
                $allowed[] = $plan->id;
            } else {
                $blocked[] = ['id' => (int) $plan->id, 'code' => $plan->code, 'title' => $plan->title, 'report' => $gate['report']];
            }
        }
        $n = $allowed ? DB::table('app_article_plan')->whereIn('id', $allowed)
            ->update(['ai_status' => 'queued', 'ai_error' => null, 'ai_queued_at' => now(), 'updated_at' => now()]) : 0;

        return response()->json(['queued' => $n, 'blocked' => $blocked] + $this->aiSummary());
    }

    public function aiCancel()
    {
        DB::table('app_article_plan')->where('ai_status', 'queued')->update(['ai_status' => null, 'ai_queued_at' => null]);

        return response()->json($this->aiSummary());
    }

    public function aiStatus()
    {
        return response()->json($this->aiSummary());
    }

    private function aiSummary(): array
    {
        $rows = DB::table('app_article_plan')->whereNotNull('ai_status')->get(['code', 'title', 'ai_status', 'ai_error', 'ai_finished_at', 'article_id']);
        $running = $rows->firstWhere('ai_status', 'running');
        $cost = (float) DB::table('app_article')->whereNotNull('ai_meta')->get(['ai_meta'])
            ->sum(fn ($a) => (float) (json_decode($a->ai_meta, true)['cost_usd'] ?? 0));

        return [
            'configured' => (bool) config('services.anthropic.key'),
            'queued' => $rows->where('ai_status', 'queued')->count(),
            'done' => $rows->where('ai_status', 'done')->count(),
            'errors' => $rows->where('ai_status', 'error')->map(fn ($r) => ['code' => $r->code, 'title' => $r->title, 'error' => $r->ai_error])->values(),
            'running' => $running ? ['code' => $running->code, 'title' => $running->title] : null,
            'costUsd' => round($cost, 2),
        ];
    }

    /** Creates the draft article for a plan item (title, focus keyword, category from the plan). */
    public function start(Request $request, int $id)
    {
        $plan = DB::table('app_article_plan')->where('id', $id)->first();
        abort_if(!$plan, 404);
        if ($plan->article_id && AppArticle::where('id', $plan->article_id)->exists()) {
            return response()->json(['articleId' => (int) $plan->article_id]);
        }
        $gate = SeoGuard::gate('plan_start', 'plan', $id, SeoGuard::candidateFromPlan($plan), Auth::guard('admin')->id(), $request->input('seoOverrideReason'));
        if (!$gate['allowed']) {
            return response()->json(['message' => 'Từ khóa của mục này đã có URL sở hữu.', 'seo' => $gate['report'], 'canOverride' => $gate['canOverride']], 409);
        }

        $lang = ArticleController::LANGS[$plan->lang] ?? 2;
        $categoryId = $plan->category_slug
            ? DB::table('app_category')->where('type', config('backend.categoryType.article'))
                ->where('language_id', $lang)->where('slug', $plan->category_slug)->value('id')
            : null;

        $a = new AppArticle();
        $a->language_id = $lang;
        $a->title = $plan->title;
        $a->category_id = $categoryId;
        $a->slug = AppArticleService::resolveUniqueSlug($plan->title, $categoryId, $lang);
        $a->focus_keyword = $plan->primary_kw;
        $a->evidence_status = $plan->evidence;
        $a->is_published = 0;
        $a->is_featured = 0;
        $a->publish_date = now();
        $a->lead = '';
        $a->content = '';
        $a->save();

        DB::table('app_article_plan')->where('id', $id)->update(['article_id' => $a->id, 'updated_at' => now()]);

        return response()->json(['articleId' => (int) $a->id], 201);
    }

    public static function item($p): array
    {
        return [
            'id' => (int) $p->id,
            'code' => $p->code,
            'pillar' => $p->pillar,
            'pillarName' => $p->pillar_name,
            'kind' => $p->kind,
            'lang' => $p->lang,
            'categorySlug' => $p->category_slug,
            'title' => $p->title,
            'primaryKw' => $p->primary_kw,
            'secondaryKw' => $p->secondary_kw,
            'intent' => $p->intent,
            'words' => $p->words ? (int) $p->words : null,
            'priority' => (int) $p->priority,
            'targetMonth' => $p->target_month,
            'moneyLinks' => json_decode($p->money_links ?: '[]', true) ?: [],
            'siblings' => json_decode($p->siblings ?: '[]', true) ?: [],
            'note' => $p->note,
            'evidence' => $p->evidence ?? null,
            'aiStatus' => $p->ai_status ?? null,
            'aiError' => $p->ai_error ?? null,
            'status' => $p->status,
            'articleId' => $p->article_id ? (int) $p->article_id : null,
        ];
    }
}
