<?php

namespace App\Http\Controllers\Api\Editor;

use App\Services\Content\ContentTranslations;
use App\Support\FrontendRevalidator;
use App\Support\HtmlSanitizer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\BackEnd\Entities\AppArticle;
use Modules\BackEnd\Helpers\Logging;
use Modules\BackEnd\Services\AppArticleService;

/**
 * Articles for the writing app. Writes the same app_article rows as the CMS article module, so the
 * Content API and the public site need no change. Status is derived:
 *   draft = not published, scheduled = published with a future publish_date, published = live.
 */
class ArticleController extends Controller
{
    public const LANGS = ['vi' => 1, 'en' => 2];
    /** verified | ops (needs operations confirmation) | design (design estimate) | na (not available yet). */
    public const EVIDENCE = ['verified', 'ops', 'design', 'na'];

    public function index(Request $request)
    {
        $q = AppArticle::query()
            ->leftJoin('app_category', 'app_category.id', '=', 'app_article.category_id')
            ->leftJoin('app_article_plan', 'app_article_plan.article_id', '=', 'app_article.id')
            ->select('app_article.id', 'app_article.language_id', 'app_article.category_id', 'app_article.title', 'app_article.slug',
                'app_article.publish_date', 'app_article.is_published', 'app_article.is_featured', 'app_article.image_link',
                'app_article.focus_keyword', 'app_article.evidence_status', 'app_article.created_at', 'app_article.updated_at',
                DB::raw('(app_article.ai_meta IS NOT NULL) as ai_draft'),
                'app_category.name as category_name', 'app_category.slug as category_slug', 'app_article_plan.code as plan_code');

        if ($lang = self::LANGS[$request->query('lang')] ?? null) {
            $q->where('app_article.language_id', $lang);
        }
        if ($request->query('ai')) {
            $q->whereNotNull('app_article.ai_meta');
        }
        if ($cat = (int) $request->query('category')) {
            $q->where('app_article.category_id', $cat);
        }
        if ($kw = trim((string) $request->query('q'))) {
            $q->where(fn ($w) => $w->where('app_article.title', 'like', "%$kw%")->orWhere('app_article.focus_keyword', 'like', "%$kw%"));
        }
        $now = now();
        match ($request->query('status')) {
            'draft' => $q->where('app_article.is_published', 0),
            'scheduled' => $q->where('app_article.is_published', 1)->where('app_article.publish_date', '>', $now),
            'published' => $q->where('app_article.is_published', 1)->where(fn ($w) => $w->whereNull('app_article.publish_date')->orWhere('app_article.publish_date', '<=', $now)),
            default => null,
        };

        $perPage = 30;
        $page = max(1, (int) $request->query('page', 1));
        $total = (clone $q)->count();
        $rows = $q->orderByRaw('IFNULL(app_article.updated_at, app_article.created_at) DESC')
            ->offset(($page - 1) * $perPage)->limit($perPage)->get();

        return response()->json([
            'items' => $rows->map(fn ($a) => self::summary($a))->all(),
            'total' => $total,
            'page' => $page,
            'lastPage' => max(1, (int) ceil($total / $perPage)),
        ]);
    }

    public function show(int $id)
    {
        return response()->json(['article' => self::detail(self::findOrFail($id))]);
    }

    public function store(Request $request)
    {
        $lang = self::LANGS[$request->input('lang')] ?? null;
        if (!$lang) {
            throw ValidationException::withMessages(['lang' => 'Chọn ngôn ngữ (vi hoặc en).']);
        }

        $a = new AppArticle();
        $a->language_id = $lang;
        $a->title = trim((string) $request->input('title')) ?: ($lang === 1 ? 'Bài viết mới' : 'Untitled article');
        $a->is_published = 0;
        $a->is_featured = 0;
        $a->publish_date = now();
        $a->lead = '';
        $a->content = '';
        $this->fill($a, $request);
        $a->save();

        Logging::logInfo('Editor: thêm tin tức.', 'id = ' . $a->id);

        return response()->json(['article' => self::detail(self::findOrFail($a->id))], 201);
    }

    public function update(Request $request, int $id)
    {
        $a = self::findOrFail($id);
        $wasLive = self::status($a) !== 'draft';
        $this->fill($a, $request);
        $a->save();

        if ($wasLive || self::status($a) !== 'draft') {
            FrontendRevalidator::later(['articles', 'legal', 'article:' . $a->id]);
        }
        Logging::logInfo('Editor: sửa tin tức.', 'id = ' . $a->id);

        return response()->json(['article' => self::detail(self::findOrFail($a->id))]);
    }

    public function destroy(int $id)
    {
        $a = self::findOrFail($id);
        $wasLive = self::status($a) !== 'draft';
        DB::transaction(function () use ($a) {
            DB::table('content_translations')->where('entity_type', 'article')->where('entity_id', $a->id)->delete();
            DB::table('app_article_plan')->where('article_id', $a->id)->update(['article_id' => null, 'updated_at' => now()]);
            $a->delete();
        });
        if ($wasLive) {
            FrontendRevalidator::later(['articles', 'legal', 'content']);
        }
        Logging::logInfo('Editor: xóa tin tức.', 'id = ' . $id);

        return response()->json(['ok' => true]);
    }

    /** Creates (or returns) the draft in the other language, linked as its translation. */
    public function translate(int $id)
    {
        $a = self::findOrFail($id);
        $otherLang = (int) $a->language_id === 1 ? 2 : 1;
        if ($existing = ContentTranslations::linkedId('article', $a->id, $otherLang)) {
            return response()->json(['article' => self::detail(self::findOrFail($existing))]);
        }

        $copy = new AppArticle();
        $copy->language_id = $otherLang;
        foreach (['title', 'sub_title', 'lead', 'content', 'image_link', 'seo_title', 'seo_description', 'focus_keyword', 'evidence_status'] as $f) {
            $copy->{$f} = $a->{$f} ?? ($f === 'lead' || $f === 'content' ? '' : null);
        }
        $copy->category_id = $a->category_id
            ? ContentTranslations::linkedId('article_category', (int) $a->category_id, $otherLang)
            : null;
        $copy->slug = AppArticleService::resolveUniqueSlug($a->slug ?: $a->title, $copy->category_id, $otherLang);
        $copy->is_published = 0;
        $copy->is_featured = 0;
        $copy->publish_date = now();
        $copy->save();

        ContentTranslations::link('article', [$a->id, $copy->id]);
        Logging::logInfo('Editor: tạo bản dịch.', 'id = ' . $a->id . ' -> ' . $copy->id);

        return response()->json(['article' => self::detail(self::findOrFail($copy->id))], 201);
    }

    /** {otherId: int} links two articles of different languages; {otherId: null} unlinks. */
    public function link(Request $request, int $id)
    {
        $a = self::findOrFail($id);
        $other = $request->input('otherId');
        if ($other) {
            $b = self::findOrFail((int) $other);
            if ((int) $b->language_id === (int) $a->language_id) {
                throw ValidationException::withMessages(['otherId' => 'Bản dịch phải khác ngôn ngữ.']);
            }
            ContentTranslations::link('article', [$a->id, $b->id]);
        } else {
            ContentTranslations::unlink('article', $a->id);
        }
        FrontendRevalidator::later(['content']);

        return response()->json(['article' => self::detail(self::findOrFail($a->id))]);
    }

    public function categories()
    {
        $rows = DB::table('app_category')->where('type', config('backend.categoryType.article'))
            ->where('slug', '!=', 'root')->orderBy('language_id')->orderBy('lft')
            ->get(['id', 'language_id', 'name', 'slug', 'lvl']);
        $out = ['vi' => [], 'en' => []];
        foreach ($rows as $r) {
            $out[array_search((int) $r->language_id, self::LANGS, true) ?: 'vi'][] = [
                'id' => (int) $r->id, 'name' => $r->name, 'slug' => $r->slug, 'level' => (int) $r->lvl,
            ];
        }

        return response()->json($out);
    }

    private function fill(AppArticle $a, Request $request): void
    {
        $in = $request->validate([
            'title' => 'sometimes|nullable|string|max:255',
            'subTitle' => 'sometimes|nullable|string|max:255',
            'lead' => 'sometimes|nullable|string|max:4000',
            'content' => 'sometimes|nullable|string',
            'slug' => 'sometimes|nullable|string|max:255',
            'categoryId' => 'sometimes|nullable|integer',
            'publishDate' => 'sometimes|nullable|date',
            'imageLink' => 'sometimes|nullable|string|max:255',
            'isFeatured' => 'sometimes|boolean',
            'isPublished' => 'sometimes|boolean',
            'seoTitle' => 'sometimes|nullable|string|max:65',
            'seoDescription' => 'sometimes|nullable|string|max:255',
            'focusKeyword' => 'sometimes|nullable|string|max:255',
            'evidenceStatus' => 'sometimes|nullable|in:' . implode(',', self::EVIDENCE),
        ]);

        $map = ['title' => 'title', 'subTitle' => 'sub_title', 'imageLink' => 'image_link',
            'seoTitle' => 'seo_title', 'seoDescription' => 'seo_description', 'focusKeyword' => 'focus_keyword', 'evidenceStatus' => 'evidence_status'];
        foreach ($map as $key => $col) {
            if (array_key_exists($key, $in)) {
                $a->{$col} = $in[$key] === null ? null : trim($in[$key]);
            }
        }
        if (array_key_exists('lead', $in)) {
            $a->lead = $in['lead'] === null ? '' : (string) HtmlSanitizer::clean(trim($in['lead']));
        }
        if (array_key_exists('content', $in)) {
            $a->content = (string) HtmlSanitizer::clean($in['content'] ?? '');
        }
        if (array_key_exists('isFeatured', $in)) {
            $a->is_featured = $in['isFeatured'] ? 1 : 0;
        }
        if (array_key_exists('isPublished', $in)) {
            $a->is_published = $in['isPublished'] ? 1 : 0;
        }
        if (array_key_exists('publishDate', $in)) {
            // The app sends Vietnam local time ("2026-10-21T08:00"); the DB stores app-timezone datetimes.
            $a->publish_date = $in['publishDate'] ? Carbon::parse($in['publishDate'], config('app.timezone')) : now();
        }
        if (array_key_exists('categoryId', $in)) {
            $cat = $in['categoryId'];
            if ($cat && !DB::table('app_category')->where('id', $cat)->where('type', config('backend.categoryType.article'))
                ->where('language_id', $a->language_id)->where('slug', '!=', 'root')->exists()) {
                throw ValidationException::withMessages(['categoryId' => 'Chuyên mục không thuộc ngôn ngữ của bài.']);
            }
            $a->category_id = $cat ?: null;
        }
        if (array_key_exists('slug', $in) || array_key_exists('categoryId', $in) || !$a->slug) {
            $source = !empty($in['slug']) ? $in['slug'] : ($a->slug ?: $a->title);
            $a->slug = AppArticleService::resolveUniqueSlug($source, $a->category_id, $a->language_id, $a->id);
        }

        if ((int) $a->is_published === 1) {
            $missing = array_filter([
                'title' => trim((string) $a->title) === '' ? 'Thiếu tiêu đề.' : null,
                'categoryId' => !$a->category_id ? 'Chọn chuyên mục trước khi đăng.' : null,
                'content' => trim(strip_tags((string) $a->content)) === '' ? 'Bài chưa có nội dung.' : null,
            ]);
            if ($missing) {
                throw ValidationException::withMessages($missing);
            }
        }
    }

    private static function findOrFail(int $id): AppArticle
    {
        $a = AppArticle::leftJoin('app_category', 'app_category.id', '=', 'app_article.category_id')
            ->where('app_article.id', $id)
            ->select('app_article.*', 'app_category.name as category_name', 'app_category.slug as category_slug')
            ->first();
        abort_if(!$a, 404, 'Không tìm thấy bài viết.');

        return $a;
    }

    public static function status($a): string
    {
        if ((int) $a->is_published !== 1) {
            return 'draft';
        }
        $date = $a->getRawOriginal('publish_date') ?? $a->publish_date;

        return $date && Carbon::parse($date)->isFuture() ? 'scheduled' : 'published';
    }

    public static function url($a): ?string
    {
        if (!$a->category_slug || !$a->slug) {
            return null;
        }

        return ((int) $a->language_id === 1 ? '/vi' : '') . '/blog/' . $a->category_slug . '/' . $a->slug . '.html';
    }

    private static function date($value): ?string
    {
        return $value ? Carbon::parse($value)->format('Y-m-d\TH:i') : null;
    }

    private static function summary($a): array
    {
        $lang = array_search((int) $a->language_id, self::LANGS, true);
        [$linked, $translationId] = ContentTranslations::lookup('article', (int) $a->id, $lang === 'vi' ? 2 : 1);

        return [
            'id' => (int) $a->id,
            'lang' => $lang,
            'title' => $a->title,
            'slug' => $a->slug,
            'categoryId' => $a->category_id ? (int) $a->category_id : null,
            'categoryName' => $a->category_name,
            'status' => self::status($a),
            'publishDate' => self::date($a->getRawOriginal('publish_date')),
            'isFeatured' => (bool) $a->is_featured,
            'imageLink' => $a->image_link ?: null,
            'focusKeyword' => $a->focus_keyword,
            'evidenceStatus' => $a->evidence_status ?? null,
            'aiDraft' => (bool) ($a->ai_draft ?? ($a->ai_meta ?? null)),
            'url' => self::url($a),
            'translationId' => $linked ? $translationId : null,
            'planCode' => $a->plan_code ?? null,
            'updatedAt' => self::date($a->getRawOriginal('updated_at') ?: $a->getRawOriginal('created_at')),
        ];
    }

    private static function detail(AppArticle $a): array
    {
        $out = self::summary($a);
        $plan = DB::table('app_article_plan')->where('article_id', $a->id)->first();
        $translation = null;
        if ($out['translationId'] && ($t = self::findOrFail($out['translationId']))) {
            $translation = ['id' => (int) $t->id, 'lang' => array_search((int) $t->language_id, self::LANGS, true),
                'title' => $t->title, 'status' => self::status($t), 'url' => self::url($t)];
        }

        return $out + [
            'subTitle' => $a->sub_title,
            'lead' => $a->lead,
            'content' => $a->content ?? '',
            'seoTitle' => $a->seo_title,
            'seoDescription' => $a->seo_description,
            'isPublished' => (int) $a->is_published === 1,
            'translation' => $translation,
            'aiMeta' => $a->ai_meta ? json_decode($a->ai_meta, true) : null,
            'plan' => $plan ? PlanController::item($plan) : null,
        ];
    }
}
