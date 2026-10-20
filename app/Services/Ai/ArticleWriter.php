<?php

namespace App\Services\Ai;

use Anthropic\Beta\Messages\BetaRawContentBlockDeltaEvent;
use Anthropic\Beta\Messages\BetaRawMessageDeltaEvent;
use Anthropic\Beta\Messages\BetaRawMessageStartEvent;
use Anthropic\Beta\Messages\BetaTextDelta;
use Anthropic\Beta\Messages\BetaWebSearchTool20260209;
use Anthropic\Client;
use App\Http\Controllers\Api\Editor\ArticleController;
use App\Support\HtmlSanitizer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\BackEnd\Entities\AppArticle;
use Modules\BackEnd\Services\AppArticleService;
use RuntimeException;

/**
 * Drafts one blog article for a content-plan item with Claude, in four steps:
 *   1. research   web search for the reader's questions and destination facts, with sources
 *   2. write      the article as JSON (title, SEO fields, lead, HTML) from the plan brief, the confirmed
 *                 brand facts (resources/ai/brand-facts.md), the style guide and the live product data
 *                 (the site's llms-full.txt)
 *   3. review     a second pass that checks facts, banned claims, links and style and returns a fixed draft
 *   4. validate   in code: only known internal links survive, then the draft is saved unpublished and linked
 *                 to the plan item; open questions and sources go to app_article.ai_meta for the editor.
 * Images and video are left as [IMAGE: …] / [VIDEO: …] placeholders; unknown facts as [CẦN XÁC NHẬN: …].
 */
class ArticleWriter
{
    /** USD per million tokens (Claude Opus 5.5) and per web search; for the cost estimate in ai_meta. */
    private const PRICE = ['input' => 4.0, 'output' => 20.0, 'cache_read' => 0.20, 'cache_write_1h' => 8.0, 'search' => 0.01];

    private const BETAS = ['server-side-fallback-2026-07-01'];

    private Client $client;
    private string $model;
    private string $site;
    private ?string $productFacts = null;
    private array $linkLists = [];
    private array $usage = ['input' => 0, 'output' => 0, 'cache_read' => 0, 'cache_write' => 0, 'searches' => 0];

    public function __construct()
    {
        $key = (string) config('services.anthropic.key');
        if ($key === '') {
            throw new RuntimeException('ANTHROPIC_API_KEY is not set');
        }
        $headers = [];
        if ($ws = config('services.anthropic.workspace')) {
            $headers['anthropic-workspace-id'] = $ws;
        }
        $this->client = new Client(apiKey: $key, requestOptions: ['extraHeaders' => $headers, 'timeout' => 900.0, 'maxRetries' => 3]);
        $this->model = (string) config('services.anthropic.model', 'claude-opus-5-5');
        $this->site = rtrim((string) config('services.anthropic.site'), '/');
    }

    /** @return int the new article id */
    public function draft(object $plan): int
    {
        $this->usage = array_map(fn () => 0, $this->usage);
        $lang = $plan->lang === 'vi' ? 'vi' : 'en';
        $brief = $this->brief($plan);
        $links = $this->linkList($lang);

        [$research, $researchSources] = $this->research($plan, $brief);
        $draft = $this->write($brief, $links, $research, $lang);
        $reviewed = $this->review($brief, $links, $draft, $lang);

        [$html, $removedLinks] = $this->keepKnownLinks($reviewed['html'], $links['paths']);

        return $this->save($plan, $lang, $reviewed, $html, [
            'model' => $this->model,
            'generated_at' => now()->toIso8601String(),
            // The review merges the writer's open questions into its own list.
            'needs_confirmation' => array_values(array_unique($reviewed['needs_confirmation'] ?? [])),
            'sources' => $this->mergeSources($reviewed['sources'] ?? [], $researchSources),
            'review_notes' => $reviewed['notes'] ?? '',
            'removed_links' => $removedLinks,
            'flags' => $this->flags($html, $lang),
            'usage' => $this->usage,
            'cost_usd' => $this->cost(),
        ]);
    }

    /* ---------------------------------------------------------------- context */

    private function systemBlocks(): array
    {
        // Identical for every article and language, so the long prefix is cached once for the whole run.
        $text = "You write blog drafts for greenrubycruises.com, the website of Green Ruby Cruises (Ha Long Bay and Lan Ha Bay, Vietnam). "
            . "A human editor reviews every draft before publishing, adds the real photos and confirms open facts. "
            . "Accuracy matters more than anything else: never invent facts about Green Ruby, and follow the style guide exactly.\n\n"
            . "<style_guide>\n" . file_get_contents(resource_path('ai/style-guide.md')) . "\n</style_guide>\n\n"
            . "<brand_facts>\n" . file_get_contents(resource_path('ai/brand-facts.md')) . "\n</brand_facts>\n\n"
            . "<product_data source=\"{$this->site}/llms-full.txt\">\n"
            . "Live data from the website (itinerary programmes, cabins, experiences, FAQ, policies). Where it conflicts with brand_facts, brand_facts wins.\n"
            . $this->productFacts() . "\n</product_data>";

        return [['type' => 'text', 'text' => $text, 'cacheControl' => ['type' => 'ephemeral', 'ttl' => '1h']]];
    }

    private function productFacts(): string
    {
        if ($this->productFacts === null) {
            $res = Http::timeout(60)->get($this->site . '/llms-full.txt');
            if (!$res->successful() || strlen($res->body()) < 2000) {
                throw new RuntimeException('Could not load llms-full.txt (' . $res->status() . ')');
            }
            $this->productFacts = $res->body();
        }

        return $this->productFacts;
    }

    /** Internal pages the draft may link to: sitemap URLs of the language, plus published article titles. */
    private function linkList(string $lang): array
    {
        if (isset($this->linkLists[$lang])) {
            return $this->linkLists[$lang];
        }
        $paths = [];
        $index = Http::timeout(30)->get($this->site . '/sitemap.xml')->body();
        preg_match_all('#<loc>([^<]+)</loc>#', $index, $maps);
        foreach ($maps[1] as $map) {
            $xml = Http::timeout(30)->get(trim($map))->body();
            preg_match_all('#<loc>([^<]+)</loc>#', $xml, $locs);
            foreach ($locs[1] as $loc) {
                $path = rtrim(parse_url(trim($loc), PHP_URL_PATH) ?: '/', '/') ?: '/';
                $isVi = $path === '/vi' || str_starts_with($path, '/vi/');
                if (($lang === 'vi') === $isVi) {
                    $paths[$path] = $this->titleFromPath($path);
                }
            }
        }
        $articles = AppArticle::leftJoin('app_category', 'app_category.id', '=', 'app_article.category_id')
            ->where('app_article.is_published', 1)->where('app_article.publish_date', '<=', now())
            ->where('app_article.language_id', ArticleController::LANGS[$lang])
            ->get(['app_article.title', 'app_article.slug', 'app_article.language_id', 'app_category.slug as category_slug']);
        foreach ($articles as $a) {
            if ($url = ArticleController::url($a)) {
                $paths[$url] = 'Article: ' . $a->title;
            }
        }
        $lines = [];
        foreach ($paths as $p => $t) {
            $lines[] = "- {$p} | {$t}";
        }

        return $this->linkLists[$lang] = ['paths' => array_keys($paths), 'text' => implode("\n", $lines)];
    }

    private function titleFromPath(string $path): string
    {
        if ($path === '/' || $path === '/vi') {
            return 'Home';
        }
        $last = basename($path, '.html');

        return ucfirst(trim(str_replace('-', ' ', preg_replace('/(-\d+)+$/', '', $last))));
    }

    private function brief(object $plan): string
    {
        $all = DB::table('app_article_plan')->get()->keyBy('code');
        $articleUrl = function ($code) use ($all) {
            $p = $all[$code] ?? null;
            if (!$p || !$p->article_id) {
                return null;
            }
            $a = AppArticle::leftJoin('app_category', 'app_category.id', '=', 'app_article.category_id')
                ->where('app_article.id', $p->article_id)->where('app_article.is_published', 1)->where('app_article.publish_date', '<=', now())
                ->first(['app_article.slug', 'app_article.language_id', 'app_category.slug as category_slug']);

            return $a ? ArticleController::url($a) : null;
        };

        $money = [];
        foreach (json_decode($plan->money_links ?: '[]', true) ?: [] as $m) {
            $target = $m['path'] ?? (isset($m['code']) ? ($articleUrl($m['code']) ?? "(article {$m['code']} not published yet, do not link)") : null);
            $money[] = '- ' . ($m['label'] ?? $m['code'] ?? '') . " -> {$target} (suggested anchor: \"{$m['anchor']}\")";
        }
        $siblings = [];
        foreach (json_decode($plan->siblings ?: '[]', true) ?: [] as $code) {
            $s = $all[$code] ?? null;
            if ($s) {
                $url = $articleUrl($code);
                $siblings[] = "- {$code} \"{$s->title}\" (keyword: {$s->primary_kw}) -> " . ($url ?? 'not published yet, do not link');
            }
        }
        $pillar = null;
        if ($plan->kind === 'cluster') {
            $p = $all->first(fn ($x) => $x->pillar === $plan->pillar && $x->kind === 'pillar');
            if ($p) {
                $pillar = "{$p->code} \"{$p->title}\" (keyword: {$p->primary_kw}) -> " . ($articleUrl($p->code) ?? 'not published yet, do not link');
            }
        }
        $evidence = ['verified' => 'facts available', 'ops' => 'some service details need operations confirmation: use [CẦN XÁC NHẬN: …] placeholders for anything not in brand_facts', 'design' => 'contains design figures: label every one as a design estimate', 'na' => 'about a service that is not available yet: describe it as planned only'][$plan->evidence] ?? 'unknown';

        return implode("\n", array_filter([
            "Plan item: {$plan->code} ({$plan->kind}) in pillar \"{$plan->pillar_name}\"",
            'Language: ' . ($plan->lang === 'vi' ? 'Vietnamese (write the whole article in Vietnamese)' : 'English'),
            "Working title: {$plan->title}",
            "Primary keyword: {$plan->primary_kw}",
            "Secondary keywords: {$plan->secondary_kw}",
            'Search intent: ' . (['T' => 'transactional', 'C' => 'commercial investigation', 'I' => 'informational'][$plan->intent] ?? 'informational'),
            "Target length: about {$plan->words} words",
            "Evidence status: {$evidence}",
            $plan->note ? "Editor's note: {$plan->note}" : null,
            $pillar ? "Pillar article of this cluster: {$pillar}" : 'This is the pillar article of its cluster.',
            $money ? "Required links:\n" . implode("\n", $money) : null,
            $siblings ? "Sibling articles:\n" . implode("\n", $siblings) : null,
        ]));
    }

    /* ---------------------------------------------------------------- model calls */

    /** @return array{0: string, 1: array} research notes and the sources found */
    private function research(object $plan, string $brief): array
    {
        $prompt = "<brief>\n{$brief}\n</brief>\n\n"
            . "Research this article before it is written. Use web search to find: what people searching \"{$plan->primary_kw}\" want to know (the questions top results answer), "
            . "and accurate, current facts about the places, weather, transport, culture or rules the article will cover. Prefer official and reputable sources. "
            . "Do not research Green Ruby itself: its facts are in brand_facts and product_data.\n\n"
            . "Return concise research notes in English: 1) reader questions to answer, 2) facts, each followed by its source URL, 3) points that are uncertain or that change often (fees, hours, regulations).";

        $messages = [['role' => 'user', 'content' => $prompt]];
        $notes = '';
        $sources = [];
        for ($turn = 0; $turn < 4; $turn++) {
            $res = $this->client->beta->messages->create(
                model: $this->model,
                maxTokens: 16000,
                system: $this->systemBlocks(),
                messages: $messages,
                thinking: ['type' => 'adaptive'],
                outputConfig: ['effort' => 'medium'],
                tools: [BetaWebSearchTool20260209::with(maxUses: 6)],
                betas: self::BETAS,
                fallbacks: 'default',
            );
            $this->addUsage($res->usage ?? null);
            foreach ($res->content as $block) {
                if ($block->type === 'text') {
                    $notes .= $block->text;
                } elseif ($block->type === 'web_search_tool_result' && is_array($block->content)) {
                    foreach ($block->content as $r) {
                        if (($r->type ?? '') === 'web_search_result') {
                            $sources[$r->url] = ['title' => $r->title, 'url' => $r->url];
                        }
                    }
                }
            }
            if ($res->stopReason === 'refusal') {
                throw new RuntimeException('Research declined by the model');
            }
            if ($res->stopReason !== 'pause_turn') {
                break;
            }
            // Long searches pause; send the turn back unchanged so the server continues it.
            $messages[] = ['role' => 'assistant', 'content' => $res->content];
        }

        return [trim($notes), array_values($sources)];
    }

    private function write(string $brief, array $links, string $research, string $lang): array
    {
        $prompt = "<brief>\n{$brief}\n</brief>\n\n"
            . "<internal_links language=\"{$lang}\">\nThe only internal URLs you may link to (path | page):\n{$links['text']}\n</internal_links>\n\n"
            . "<research_notes>\n{$research}\n</research_notes>\n\n"
            . "Write the article now, following the style guide and using only brand_facts and product_data for anything about Green Ruby and research_notes (with their sources) for everything else.\n"
            . "Fields: title (the H1, natural, contains the primary keyword or a close variant, no year unless the brief needs one); slug (short, lowercase, hyphens, no year); "
            . "seo_title (max 60 characters including spaces, contains the primary keyword; the site adds the brand only if missing, so you may end with \" | Green Ruby\" when it fits); "
            . "seo_description (120 to 155 characters, contains the primary keyword); lead (one or two sentences shown under the title and in the blog list, plain text); "
            . "html (the article body as HTML per the style guide, starting with the direct-answer paragraph, with [IMAGE]/[VIDEO] placeholders and [CẦN XÁC NHẬN: …] where facts are missing); "
            . "needs_confirmation (every open question the editor must answer before publishing, written in Vietnamese); sources (the external sources you relied on); notes (short notes to the editor in Vietnamese).";

        return $this->structured($prompt, 'high');
    }

    private function review(string $brief, array $links, array $draft, string $lang): array
    {
        $prompt = "<brief>\n{$brief}\n</brief>\n\n"
            . "<internal_links language=\"{$lang}\">\n{$links['text']}\n</internal_links>\n\n"
            . "<draft>\n" . json_encode($draft, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n</draft>\n\n"
            . "You are the editor. Check the draft line by line and return the corrected article in the same fields:\n"
            . "- Every statement about Green Ruby must match brand_facts or product_data exactly; rewrite or replace with [CẦN XÁC NHẬN: …] anything that does not (dates, prices, certifications, services, reviews, awards).\n"
            . "- Remove banned wording, hype and any em dash character; keep design figures labelled as design estimates; Halal and Green Globe only as in progress.\n"
            . "- Keep only internal links from the list; make anchors descriptive; make sure required links that are linkable are present.\n"
            . "- Check structure: direct answer first, question-style H2s, table where comparing, placeholders for media, no H1, allowed tags only.\n"
            . "- Check seo_title length (60 max) and seo_description length (120 to 155).\n"
            . "- Voice: it must read like an experienced travel specialist giving specific, honest advice. Rewrite every sentence that uses the banned AI-slop wording or says something generic that could describe any destination. Remove invented experiences or anecdotes.\n"
            . "- Keywords and links: the primary keyword appears naturally in the title, the first two sentences, one H2 and near the end, never stuffed; internal links sit inside sentences with descriptive anchors.\n"
            . "In notes, list in Vietnamese what you changed and anything the editor should look at. needs_confirmation: one merged list without duplicates (combine questions about the same thing into one), in Vietnamese.";

        return $this->structured($prompt, 'medium');
    }

    /** Streams one structured-output call and returns the decoded JSON. */
    private function structured(string $prompt, string $effort): array
    {
        $schema = [
            'type' => 'object',
            'properties' => [
                'title' => ['type' => 'string'],
                'slug' => ['type' => 'string'],
                'seo_title' => ['type' => 'string'],
                'seo_description' => ['type' => 'string'],
                'lead' => ['type' => 'string'],
                'html' => ['type' => 'string'],
                'needs_confirmation' => ['type' => 'array', 'items' => ['type' => 'string']],
                'sources' => ['type' => 'array', 'items' => [
                    'type' => 'object',
                    'properties' => ['title' => ['type' => 'string'], 'url' => ['type' => 'string']],
                    'required' => ['title', 'url'],
                    'additionalProperties' => false,
                ]],
                'notes' => ['type' => 'string'],
            ],
            'required' => ['title', 'slug', 'seo_title', 'seo_description', 'lead', 'html', 'needs_confirmation', 'sources', 'notes'],
            'additionalProperties' => false,
        ];

        $stream = $this->client->beta->messages->createStream(
            model: $this->model,
            maxTokens: 64000,
            system: $this->systemBlocks(),
            messages: [['role' => 'user', 'content' => $prompt]],
            thinking: ['type' => 'adaptive'],
            outputConfig: ['effort' => $effort, 'format' => ['type' => 'json_schema', 'schema' => $schema]],
            betas: self::BETAS,
            fallbacks: 'default',
        );

        $json = '';
        $stop = null;
        foreach ($stream as $event) {
            if ($event instanceof BetaRawContentBlockDeltaEvent && $event->delta instanceof BetaTextDelta) {
                $json .= $event->delta->text;
            } elseif ($event instanceof BetaRawMessageStartEvent) {
                $this->addUsage($event->message->usage ?? null);
            } elseif ($event instanceof BetaRawMessageDeltaEvent) {
                $stop = $event->delta->stopReason ?? $stop;
                $this->usage['output'] += (int) ($event->usage->outputTokens ?? 0);
            }
        }
        if ($stop === 'refusal') {
            throw new RuntimeException('Draft declined by the model');
        }
        if ($stop === 'max_tokens') {
            throw new RuntimeException('Draft cut off at max_tokens');
        }
        $data = json_decode($json, true);
        if (!is_array($data) || empty($data['html'])) {
            throw new RuntimeException('Draft is not valid JSON (stop: ' . ($stop ?? 'none') . ')');
        }

        return $data;
    }

    /* ---------------------------------------------------------------- checks and save */

    /** Unwraps internal links that are not in the link list (keeps the text). */
    private function keepKnownLinks(string $html, array $paths): array
    {
        $known = array_flip($paths);
        $removed = [];
        $html = preg_replace_callback('#<a\s[^>]*href="([^"]*)"[^>]*>(.*?)</a>#is', function ($m) use ($known, &$removed) {
            $href = html_entity_decode($m[1]);
            $path = preg_replace('#^https?://(www\.)?greenrubycruises\.com#i', '', $href);
            $internal = str_starts_with($path, '/');
            if (!$internal) {
                return $m[0];
            }
            $clean = rtrim(strtok($path, '?#') ?: '/', '/') ?: '/';
            if (isset($known[$clean])) {
                return str_replace($m[1], htmlspecialchars($clean), $m[0]);
            }
            $removed[] = $href;

            return $m[2];
        }, $html);

        return [$html, $removed];
    }

    private function flags(string $html, string $lang): array
    {
        $text = html_entity_decode(strip_tags($html));
        $flags = [];
        $banned = '/\b(first|largest|biggest|leading|world[- ]class|award[- ]winning|eco[- ]certified|zero[- ]discharge|carbon[- ]neutral|plastic[- ]free|hybrid|michelin|butler|6[- ]star)\b|đầu tiên|duy nhất|lớn nhất|6 sao|launch(ed|ing)? in 2026|ra mắt (năm |cuối năm )?2026|—/iu';
        if (preg_match_all($banned, $text, $m)) {
            $flags[] = 'Từ cần xem lại: ' . implode(', ', array_unique(array_map('mb_strtolower', $m[0])));
        }
        $slop = '/\b(nestled|boasts?|breathtaking|stunning|picturesque|hidden gems?|paradise|unforgettable|once-in-a-lifetime|embark|immerse yourself|delve|a testament to|tapestry|symphony|oasis|treasure trove|unparalleled|seamless(ly)?|elevate|curated|bucket list|look no further|it\'s no secret|let\'s dive|in conclusion|plethora|myriad|vibrant)\b/iu';
        if (preg_match_all($slop, $text, $m)) {
            $flags[] = 'Từ "văn AI" cần viết lại: ' . implode(', ', array_unique(array_map('mb_strtolower', $m[0])));
        }
        if (preg_match('/\b(certified)\b/i', $text) && !preg_match('/not (yet )?certified|in progress|đang/i', $text)) {
            $flags[] = 'Có chữ "certified": kiểm tra lại Green Globe / Halal.';
        }
        if (preg_match('/[$€£]\s?\d|\d+\s?(usd|vnd|đồng)\b/iu', $text)) {
            $flags[] = 'Có con số giá tiền: Green Ruby chưa có giá thật.';
        }

        return $flags;
    }

    private function save(object $plan, string $lang, array $d, string $html, array $meta): int
    {
        $langId = ArticleController::LANGS[$lang];
        $categoryId = $plan->category_slug
            ? DB::table('app_category')->where('type', config('backend.categoryType.article'))->where('language_id', $langId)->where('slug', $plan->category_slug)->value('id')
            : null;

        $seoTitle = trim($d['seo_title']);
        if (mb_strlen($seoTitle) > 65) {
            $seoTitle = rtrim(mb_substr($seoTitle, 0, 62), " ,:;|-") . '…';
        }

        $a = new AppArticle();
        $a->language_id = $langId;
        $a->category_id = $categoryId;
        $a->title = mb_substr(trim($d['title']), 0, 255);
        $a->slug = AppArticleService::resolveUniqueSlug($d['slug'] ?: $d['title'], $categoryId, $langId);
        $a->lead = (string) HtmlSanitizer::clean(trim($d['lead']));
        $a->content = (string) HtmlSanitizer::clean($html);
        $a->seo_title = $seoTitle;
        $a->seo_description = mb_substr(trim($d['seo_description']), 0, 255);
        $a->focus_keyword = $plan->primary_kw;
        $a->evidence_status = $plan->evidence;
        $a->ai_meta = json_encode($meta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $a->is_published = 0;
        $a->is_featured = 0;
        $a->publish_date = now();
        $a->created_at = now();
        $a->created_by = (int) (DB::table('ad_user')->where('status', 1)->orderBy('id')->value('id') ?? 1);
        $a->save();

        DB::table('app_article_plan')->where('id', $plan->id)->update(['article_id' => $a->id, 'updated_at' => now()]);
        Log::info('AI draft saved', ['plan' => $plan->code, 'article' => $a->id, 'cost_usd' => $meta['cost_usd']]);

        return (int) $a->id;
    }

    private function mergeSources(array $a, array $b): array
    {
        $out = [];
        foreach (array_merge($a, $b) as $s) {
            if (!empty($s['url']) && preg_match('#^https?://#', $s['url'])) {
                $out[$s['url']] ??= ['title' => (string) ($s['title'] ?? $s['url']), 'url' => $s['url']];
            }
        }

        return array_values($out);
    }

    private function addUsage($u): void
    {
        if (!$u) {
            return;
        }
        $this->usage['input'] += (int) ($u->inputTokens ?? 0);
        $this->usage['cache_read'] += (int) ($u->cacheReadInputTokens ?? 0);
        $this->usage['cache_write'] += (int) ($u->cacheCreationInputTokens ?? 0);
        $this->usage['output'] += (int) ($u->outputTokens ?? 0);
        $this->usage['searches'] += (int) ($u->serverToolUse->webSearchRequests ?? 0);
    }

    private function cost(): float
    {
        $p = self::PRICE;
        $u = $this->usage;

        return round(($u['input'] * $p['input'] + $u['output'] * $p['output'] + $u['cache_read'] * $p['cache_read'] + $u['cache_write'] * $p['cache_write_1h']) / 1_000_000 + $u['searches'] * $p['search'], 4);
    }
}
