<?php
/**
 * Sets each content-patch op's `id` from its `match` (column => value), so one patch file works on databases whose
 * row ids differ (local copy vs production). Every match must find exactly one row, otherwise nothing is written.
 *
 *   php scripts/resolve-ja-ids.php database/content-patches/X.json /tmp/X.resolved.json
 *   php artisan content:patch /tmp/X.resolved.json            (dry run, then --apply)
 *
 * Ops without `match` keep their id. Prints every op whose id changed.
 */

use Illuminate\Support\Facades\DB;

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

[$self, $in, $out] = $argv + [null, null, null];
$patch = $in && is_file($in) ? json_decode((string) file_get_contents($in), true) : null;
if (!is_array($patch) || !$out) {
    fwrite(STDERR, "usage: php scripts/resolve-ja-ids.php <patch.json> <resolved.json>\n");
    exit(1);
}

$problems = 0;
foreach ($patch['ops'] as $i => &$op) {
    if (empty($op['match'])) {
        continue;
    }
    $ids = DB::table($op['table'])->where($op['match'])->pluck('id')->all();
    if (count($ids) !== 1) {
        $problems++;
        fwrite(STDERR, sprintf("#%d %s %s: %d rows match\n", $i + 1, $op['table'], json_encode($op['match'], JSON_UNESCAPED_UNICODE), count($ids)));
        continue;
    }
    if ((string) $ids[0] !== (string) $op['id']) {
        echo sprintf("#%d %s.%s: id %s -> %s\n", $i + 1, $op['table'], $op['column'], $op['id'], $ids[0]);
    }
    $op['id'] = (string) $ids[0];
}
unset($op);

if ($problems) {
    fwrite(STDERR, "$problems op(s) did not resolve; nothing written.\n");
    exit(1);
}
file_put_contents($out, json_encode($patch, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
echo "Resolved " . count($patch['ops']) . " ops -> $out\n";
