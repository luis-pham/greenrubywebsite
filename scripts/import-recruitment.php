<?php
/**
 * Imports job descriptions (Markdown with YAML front matter) into app_job, the CMS "Tuyển dụng" module.
 *
 *   php scripts/import-recruitment.php                 dry run: what would be created / updated / left unchanged
 *   php scripts/import-recruitment.php --apply         write (one transaction), then revalidate the website
 *   php scripts/import-recruitment.php --dir=PATH      other folder of *.md (default database/data/recruitment)
 *   CMS_ROOT=/var/www/html php /tmp/import-recruitment.php --dir=/tmp/recruitment --apply   (run from outside the repo)
 *
 * Upsert by slug (app_job.slug is unique; the module is Vietnamese only, front matter locale must be "vi").
 * - New slug: created as DRAFT ("Bản nháp"), next display order, no posting date (set when HR publishes).
 * - Existing slug: only the imported fields below are overwritten (after a JSON backup of the row in
 *   storage/app/recruitment-import-backups); id, status, order, posted_at, deadline, location, address, summary
 *   and SEO fields are kept. Identical content = "unchanged" (running twice changes nothing).
 * - Rows whose slug is not in the folder are never touched.
 *
 * Mapping (Markdown H2 → CMS section): bold lines before the first H2 + "MÔ TẢ CHUNG" → intro (Giới thiệu công
 * việc); "MÔ TẢ CÔNG VIỆC" / "CÁC CHỨC NĂNG, NHIỆM VỤ CHÍNH" → description; "YÊU CẦU ỨNG VIÊN" → requirements;
 * "QUYỀN LỢI" → benefits; "ỨNG TUYỂN": its e-mail / website lines are dropped (the page shows the application
 * e-mail), any other text is kept under an "Ứng tuyển" H3 at the end of the last section. The H1 is the page
 * title (front matter title). Any other H2 stops the import. Markdown → HTML with league/commonmark (raw HTML
 * escaped), then safe_html; heading / bullet / paragraph counts are checked against the source.
 */

use App\Services\Content\CareerContentService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

$root = getenv('CMS_ROOT') ?: __DIR__ . '/..';
require $root . '/vendor/autoload.php';
$app = require $root . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$options = getopt('', ['apply', 'dir:']);
$apply = isset($options['apply']);
$dir = rtrim($options['dir'] ?? $root . '/database/data/recruitment', '/');

const MANAGED = ['title', 'department', 'employment_type', 'apply_email', 'intro', 'description', 'requirements', 'benefits'];
const SECTIONS = [
    'MÔ TẢ CHUNG' => 'intro',
    'MÔ TẢ CÔNG VIỆC' => 'description',
    'CÁC CHỨC NĂNG, NHIỆM VỤ CHÍNH' => 'description',
    'YÊU CẦU ỨNG VIÊN' => 'requirements',
    'QUYỀN LỢI' => 'benefits',
    'ỨNG TUYỂN' => 'apply',
];
const EMPLOYMENT = ['FULL_TIME' => 'full-time', 'PART_TIME' => 'part-time', 'CONTRACTOR' => 'contract', 'TEMPORARY' => 'temporary', 'INTERN' => 'intern'];

function fail(string $message): never
{
    fwrite(STDERR, "ERROR: $message\n");
    exit(1);
}

/** "key: value" lines; quoted strings unquoted, integers cast. Enough for the flat front matter of these files. */
function frontMatter(string $yaml): array
{
    $data = [];
    foreach (preg_split('/\R/u', $yaml) as $line) {
        if (trim($line) === '' || str_starts_with(ltrim($line), '#')) {
            continue;
        }
        if (!preg_match('/^([A-Za-z0-9_]+):\s*(.*)$/u', $line, $m)) {
            fail("front matter line not understood: $line");
        }
        $value = trim($m[2]);
        if (preg_match('/^"(.*)"$/u', $value, $q)) {
            $value = stripcslashes($q[1]);
        } elseif (preg_match('/^-?\d+$/', $value)) {
            $value = (int) $value;
        }
        $data[$m[1]] = $value;
    }

    return $data;
}

/** Consecutive bullets separated by a blank line form one tight list (no <p> inside each <li>), text unchanged. */
function tightLists(string $md): string
{
    do {
        $before = $md;
        $md = preg_replace('/^(- .*)\R\R+(?=- )/mu', "$1\n", $md);
    } while ($md !== $before);

    return $md;
}

function toHtml(string $md): ?string
{
    $md = trim($md);
    if ($md === '') {
        return null;
    }
    $html = Str::markdown(tightLists($md), ['html_input' => 'escape', 'allow_unsafe_links' => false]);

    return trim((string) safe_html($html));
}

function counts(string $text, bool $html): array
{
    return $html
        ? ['h3' => substr_count($text, '<h3'), 'li' => substr_count($text, '<li'), 'p' => substr_count($text, '<p')]
        : [
            'h3' => preg_match_all('/^### /mu', $text),
            'li' => preg_match_all('/^- /mu', $text),
            'p' => preg_match_all('/^(?!#|- |\s*$).+(?:\R(?!#|- |\s*$).+)*/mu', $text), // paragraphs: runs of plain lines
        ];
}

/** @return array{fields: array, check: array} */
function parseJob(string $file): array
{
    $raw = str_replace("\r\n", "\n", (string) file_get_contents($file));
    if (!preg_match('/^---\n(.*?)\n---\n(.*)$/su', $raw, $m)) {
        fail(basename($file) . ': no YAML front matter');
    }
    $meta = frontMatter($m[1]);
    $body = $m[2];
    foreach (['slug', 'title', 'department'] as $key) {
        if (empty($meta[$key])) {
            fail(basename($file) . ": front matter '$key' is missing");
        }
    }
    if (($meta['locale'] ?? 'vi') !== 'vi') {
        fail(basename($file) . ': only locale vi is supported (the recruitment pages are Vietnamese only)');
    }
    if ($meta['slug'] !== Str::slug($meta['slug'])) {
        fail(basename($file) . ": slug '{$meta['slug']}' is not a URL slug");
    }

    $body = preg_replace('/^# .*\n/mu', '', $body, 1); // H1 = page title
    $parts = preg_split('/^## +(.+)$/mu', $body, -1, PREG_SPLIT_DELIM_CAPTURE);
    $sections = ['intro' => trim($parts[0]), 'description' => '', 'requirements' => '', 'benefits' => ''];
    $applyText = '';
    $usedSource = trim($parts[0]) === '' ? '' : trim($parts[0]) . "\n\n";
    for ($i = 1; $i < count($parts); $i += 2) {
        $heading = mb_strtoupper(trim(preg_replace('/^\d+\.\s*/u', '', $parts[$i])));
        $text = trim($parts[$i + 1] ?? '');
        $target = SECTIONS[$heading] ?? fail(basename($file) . ": unknown section \"{$parts[$i]}\" (add it to SECTIONS)");
        if ($target === 'apply') {
            // E-mail, website and brand lines are shown by the page itself (application block); keep anything else.
            $kept = array_filter(preg_split('/\R/u', $text), fn ($line) => !preg_match('/^\*\*(Email nhận hồ sơ:|Website:|Green Ruby Cruises\*\*)/u', trim($line)));
            $applyText = trim(preg_replace('/\n{3,}/', "\n\n", implode("\n", $kept)));
            if ($applyText !== '') {
                $usedSource .= $applyText . "\n\n";
            }
            continue;
        }
        $sections[$target] = trim($sections[$target] . "\n\n" . $text);
        $usedSource .= $text . "\n\n";
    }
    if ($applyText !== '') {
        $last = $sections['benefits'] !== '' ? 'benefits' : ($sections['requirements'] !== '' ? 'requirements' : 'description');
        $sections[$last] .= "\n\n### Ứng tuyển\n\n" . $applyText;
    }

    $type = $meta['employment_type'] ?? null;
    if ($type !== null && !isset(EMPLOYMENT[$type])) {
        fail(basename($file) . ": employment_type '$type' is not one of " . implode(', ', array_keys(EMPLOYMENT)));
    }
    $email = trim((string) ($meta['application_email'] ?? ''));
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        fail(basename($file) . ": application_email '$email' is not valid");
    }

    $fields = [
        'title' => $meta['title'],
        'department' => $meta['department'],
        'employment_type' => $type ? EMPLOYMENT[$type] : null,
        // The default address (config "recruitment-email") is not copied onto the row, so changing it once changes every posting.
        'apply_email' => $email === '' || strcasecmp($email, CareerContentService::defaultEmail()) === 0 ? null : $email,
    ];
    foreach ($sections as $key => $md) {
        $fields[$key] = toHtml($md);
    }

    $html = implode('', array_filter([$fields['intro'], $fields['description'], $fields['requirements'], $fields['benefits']]));
    $expected = counts($usedSource, false);
    $expected['h3'] += $applyText !== '' ? 1 : 0;
    $actual = counts($html, true);
    if ($expected['h3'] !== $actual['h3'] || $expected['li'] !== $actual['li'] || $expected['p'] !== $actual['p']) {
        fail(basename($file) . ': content check failed, source ' . json_encode($expected) . ' vs HTML ' . json_encode($actual));
    }
    if (mb_strlen($fields['title']) > 150 || mb_strlen($fields['department']) > 100) {
        fail(basename($file) . ': title (150) or department (100) too long for app_job');
    }

    return ['slug' => $meta['slug'], 'fields' => $fields, 'check' => $actual];
}

$files = glob($dir . '/*.md') ?: fail("no *.md in $dir");
$jobs = [];
foreach ($files as $file) {
    if (strcasecmp(basename($file), 'README.md') === 0) {
        continue;
    }
    $job = parseJob($file);
    if (isset($jobs[$job['slug']])) {
        fail("slug {$job['slug']} appears twice");
    }
    $jobs[$job['slug']] = $job;
}

echo ($apply ? 'APPLY' : 'DRY RUN (add --apply to write)') . ' — ' . count($jobs) . " job(s) from $dir\n";
$results = [];
$backupDir = storage_path('app/recruitment-import-backups');

DB::transaction(function () use ($jobs, $apply, $backupDir, &$results) {
    foreach ($jobs as $slug => $job) {
        $row = DB::table('app_job')->where('slug', $slug)->lockForUpdate()->first();
        $check = "h3={$job['check']['h3']} li={$job['check']['li']} p={$job['check']['p']}";
        if (!$row) {
            $id = null;
            if ($apply) {
                $id = DB::table('app_job')->insertGetId($job['fields'] + [
                    'slug' => $slug,
                    'status' => 'draft',
                    'ord' => (int) DB::table('app_job')->max('ord') + 1,
                    'created_at' => now(),
                    'created_by' => 1,
                ]);
            }
            $results[] = ['created', $slug, $id, 'draft', $check];
            continue;
        }
        $changed = array_values(array_filter(MANAGED, fn ($f) => (string) ($row->{$f} ?? '') !== (string) ($job['fields'][$f] ?? '')));
        if ($changed === []) {
            $results[] = ['unchanged', $slug, $row->id, $row->status, $check];
            continue;
        }
        if ($apply) {
            if (!is_dir($backupDir)) {
                mkdir($backupDir, 0775, true);
            }
            file_put_contents($backupDir . '/' . now()->format('Ymd-His') . "-{$slug}.json", json_encode((array) $row, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            DB::table('app_job')->where('id', $row->id)->update(array_intersect_key($job['fields'], array_flip($changed)) + ['updated_at' => now(), 'updated_by' => 1]);
        }
        $results[] = ['updated (' . implode(', ', $changed) . ')', $slug, $row->id, $row->status . ' (kept)', $check];
    }
});

foreach ($results as [$action, $slug, $id, $status, $check]) {
    printf("  %-60s %-36s id=%-5s %-16s %s\n", $action, $slug, $id ?? '-', $status, $check);
}

$writes = count(array_filter($results, fn ($r) => $r[0] !== 'unchanged'));
if ($apply && $writes > 0) {
    // Direct DB writes bypass the admin's RevalidateFrontend hook.
    $url = config('services.frontend_revalidate.url');
    $secret = config('services.frontend_revalidate.secret');
    if ($url && $secret) {
        try {
            $status = Http::timeout(5)->acceptJson()->post($url, ['secret' => $secret, 'tags' => ['careers']])->status();
            echo "Website revalidated (careers): HTTP $status\n";
        } catch (\Throwable $e) {
            echo "Website revalidate failed: {$e->getMessage()} (drafts are not public; published rows refresh within the hour)\n";
        }
    } else {
        echo "FRONTEND_REVALIDATE_URL not set: website not revalidated\n";
    }
}
echo $writes === 0 ? "Nothing to write.\n" : ($apply ? "Done: $writes write(s).\n" : "$writes write(s) pending.\n");
