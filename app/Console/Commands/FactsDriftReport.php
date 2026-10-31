<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Read-only report of business facts that differ between linked EN and VI rows (i18n Phase 0,
 * greenruby-web/docs/i18n-audit/12-revised-architecture.md §9). Neither language is assumed to be correct,
 * prices are never converted between currencies, and nothing is written: the command aborts if any
 * non-SELECT query ran.
 *
 *   facts:drift-report                                   table summary
 *   facts:drift-report --json=/path/report.json          full structured report
 *   facts:drift-report --operating-from=2027-02-01       departures before that date are classed "pre-operation"
 */
class FactsDriftReport extends Command
{
    protected $signature = 'facts:drift-report {--json= : write the full report to this file} {--operating-from= : YYYY-MM-DD, departures before it are classed pre-operation}';

    protected $description = 'Read-only: compare capacity, specs, prices and departures of linked EN/VI rows';

    /** Field => [business impact, where to verify]. */
    private const IMPACT = [
        'capacity' => ['Guest limit shown and used by the booking wizard differs per language', 'Ship operator cabin specification / TPX operations'],
        'over_capacity_adult' => ['Extra-adult surcharge rule differs per language', 'Reservations pricing policy'],
        'over_capacity_child_6_12' => ['Child surcharge rule differs per language', 'Reservations pricing policy'],
        'over_capacity_child_2_5' => ['Child surcharge rule differs per language', 'Reservations pricing policy'],
        'over_capacity_infant' => ['Infant rule differs per language', 'Reservations pricing policy'],
        'capacity_exceeded' => ['Over-capacity booking rule differs per language', 'Reservations pricing policy'],
        'area' => ['Displayed cabin size differs per language', 'Ship operator cabin specification'],
        'discount_percent' => ['Displayed discount differs per language', 'Sales / pricing owner'],
        'dimension_length' => ['Displayed ship length differs per language', 'Shipyard / registration documents'],
        'total_cabin' => ['Displayed cabin count differs per language', 'Ship operator specification'],
        'total_floor' => ['Displayed deck count differs per language', 'Ship operator specification'],
        'year_built' => ['Displayed build year differs per language', 'Shipyard / registration documents'],
        'star_rating' => ['Displayed rating differs per language', 'Marketing owner'],
        'duration' => ['Itinerary length differs per language (price matrix lookup uses it)', 'Itinerary product sheet'],
        'bay' => ['Itinerary bay differs per language', 'Itinerary product sheet'],
        'start_time' => ['Boarding time differs per language', 'Operations schedule'],
        'cruise_link' => ['Linked rows belong to ships that are not each other\'s translation', 'Content editor (translation links)'],
        'price_rows' => ['A duration/guest combination is bookable in one language only', 'Reservations price list (per currency)'],
        'departures' => ['Bookable dates differ per language', 'Operations departure schedule'],
        'itinerary_cruises' => ['Itinerary is sold on different ships per language', 'Itinerary product sheet'],
        'unlinked' => ['Row has no counterpart: facts cannot be compared', 'Content editor (translation links)'],
    ];

    private const CRUISE_FIELDS = ['capacity', 'dimension_length', 'total_cabin', 'total_floor', 'year_built', 'star_rating'];

    private const CABIN_FIELDS = ['capacity', 'over_capacity_adult', 'over_capacity_child_6_12', 'over_capacity_child_2_5', 'over_capacity_infant', 'capacity_exceeded', 'area', 'discount_percent'];

    private const ITINERARY_FIELDS = ['duration', 'bay', 'start_time'];

    private array $differences = [];

    public function handle(): int
    {
        DB::enableQueryLog();
        $en = (int) DB::table('ad_language')->where('code', 'en')->value('id');
        $vi = (int) DB::table('ad_language')->where('code', 'vi')->value('id');
        $operatingFrom = $this->option('operating-from') ? Carbon::parse($this->option('operating-from'))->startOfDay() : null;
        $today = Carbon::today();

        $cruisePairs = $this->pairs('cruise', 'app_cruise', $en, $vi);
        $cabinPairs = $this->pairs('cabin', 'app_cabin', $en, $vi, fn ($q) => $q->join('app_group', 'app_group.id', '=', 'app_cabin.group_id')
            ->whereIn('app_group.slug', config('backend.listAccommodationSlug')));
        $itineraryPairs = $this->pairs('itinerary', 'app_itinerary', $en, $vi);
        $cruiseMap = collect($cruisePairs['pairs'])->mapWithKeys(fn ($p) => [$p['en'] => $p['vi']])->all();

        foreach ($cruisePairs['pairs'] as $p) {
            $this->compareFields('cruise', $p, 'app_cruise', self::CRUISE_FIELDS);
        }

        foreach ($cabinPairs['pairs'] as $p) {
            $rows = $this->compareFields('cabin', $p, 'app_cabin', self::CABIN_FIELDS);
            if (($cruiseMap[$rows['en']->cruise_id] ?? null) !== (int) $rows['vi']->cruise_id) {
                $this->add('cabin', $p, 'cruise_link', $rows['en']->cruise_id, $rows['vi']->cruise_id);
            }
            $prices = fn ($id) => DB::table('app_cabin_price')->where('cabin_id', $id)->orderBy('duration')->orderBy('guest')->get()
                ->mapWithKeys(fn ($r) => [$r->duration . 'd/' . $r->guest . 'g' => (float) $r->price]);
            [$pe, $pv] = [$prices($p['en']), $prices($p['vi'])];
            $onlyEn = $pe->keys()->diff($pv->keys())->values()->all();
            $onlyVi = $pv->keys()->diff($pe->keys())->values()->all();
            if ($onlyEn || $onlyVi) {
                $this->add('cabin', $p, 'price_rows', ['rows' => $pe->count(), 'only_here' => $onlyEn, 'matrix_USD' => $pe->all()],
                    ['rows' => $pv->count(), 'only_here' => $onlyVi, 'matrix_VND' => $pv->all()]);
            }
        }

        foreach ($itineraryPairs['pairs'] as $p) {
            $this->compareFields('itinerary', $p, 'app_itinerary', self::ITINERARY_FIELDS);
            $sched = fn ($id) => DB::table('app_cruise_itinerary')->where('itinerary_id', $id)->orderBy('start_at')->get(['cruise_id', 'start_at']);
            [$se, $sv] = [$sched($p['en']), $sched($p['vi'])];
            $shipsEn = $se->pluck('cruise_id')->unique()->map(fn ($id) => $cruiseMap[$id] ?? "unlinked:$id")->sort()->values()->all();
            $shipsVi = $sv->pluck('cruise_id')->unique()->map('intval')->sort()->values()->all();
            if ($shipsEn !== $shipsVi) {
                $this->add('itinerary', $p, 'itinerary_cruises', $se->pluck('cruise_id')->unique()->values()->all(), $shipsVi);
            }
            $dates = fn (Collection $s) => $s->pluck('start_at')->map(fn ($d) => Carbon::parse($d)->format('Y-m-d'))->unique()->values();
            [$de, $dv] = [$dates($se), $dates($sv)];
            if ($de->all() !== $dv->all()) {
                $classify = fn ($d) => Carbon::parse($d)->lt($today) ? 'past' : ($operatingFrom && Carbon::parse($d)->lt($operatingFrom) ? 'pre-operation' : 'future');
                $this->add('itinerary', $p, 'departures',
                    $de->mapWithKeys(fn ($d) => [$d => $classify($d)])->all(),
                    $dv->mapWithKeys(fn ($d) => [$d => $classify($d)])->all());
            }
        }

        foreach (['cruise' => $cruisePairs, 'cabin' => $cabinPairs, 'itinerary' => $itineraryPairs] as $type => $set) {
            foreach ($set['unlinked'] as $u) {
                $this->add($type, ['group' => null, 'en' => $u['lang'] === 'en' ? $u['id'] : null, 'vi' => $u['lang'] === 'vi' ? $u['id'] : null], 'unlinked', null, null);
            }
        }

        $writes = collect(DB::getQueryLog())->reject(fn ($q) => preg_match('/^\s*select/i', $q['query']));
        if ($writes->isNotEmpty()) {
            $this->error('Aborted: a non-SELECT query ran.');

            return self::FAILURE;
        }

        $report = [
            'generated_at' => now()->toIso8601String(),
            'database' => DB::getDatabaseName(),
            'read_only' => true,
            'queries' => count(DB::getQueryLog()),
            'operating_from' => $operatingFrom?->toDateString(),
            'currency_rule' => 'en rows priced in USD, vi rows in VND (BookingPricer::currencyForLanguage); values are never converted',
            'booking_identifiers' => [
                'cruise' => $cruisePairs['pairs'],
                'cabin' => $cabinPairs['pairs'],
                'itinerary' => $itineraryPairs['pairs'],
                'note' => 'bookings.itinerary_id and booking_cabins.cabin_id store these language-specific ids',
            ],
            'differences' => $this->differences,
        ];

        $this->table(['type', 'en id', 'vi id', 'field', 'en', 'vi'], array_map(fn ($d) => [
            $d['entity_type'], $d['en_id'] ?? '-', $d['vi_id'] ?? '-', $d['field'],
            mb_strimwidth(json_encode($d['en_value'], JSON_UNESCAPED_UNICODE), 0, 60, '…'),
            mb_strimwidth(json_encode($d['vi_value'], JSON_UNESCAPED_UNICODE), 0, 60, '…'),
        ], $this->differences));
        $this->info(count($this->differences) . ' difference(s); ' . count(DB::getQueryLog()) . ' SELECT queries, 0 writes.');

        if ($path = $this->option('json')) {
            file_put_contents($path, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL);
            $this->info("Report written to $path");
        }

        return self::SUCCESS;
    }

    /** Linked EN/VI pairs of a type (explicit links only) and the in-scope rows without a counterpart. */
    private function pairs(string $type, string $table, int $en, int $vi, ?callable $scope = null): array
    {
        $q = DB::table($table)->whereIn("$table.language_id", [$en, $vi]);
        $rows = ($scope ? $scope($q) : $q)->get(["$table.id", "$table.language_id"])->keyBy('id');
        $links = DB::table('content_translations')->where('entity_type', $type)->whereIn('entity_id', $rows->keys())->orderBy('entity_id')->get()->groupBy('group_key'); // ordered: stable output
        $pairs = [];
        $linked = [];
        foreach ($links as $group => $members) {
            $byLang = $members->keyBy('language_id');
            if (isset($byLang[$en], $byLang[$vi])) {
                $pairs[] = ['group' => $group, 'en' => (int) $byLang[$en]->entity_id, 'vi' => (int) $byLang[$vi]->entity_id];
                $linked[] = (int) $byLang[$en]->entity_id;
                $linked[] = (int) $byLang[$vi]->entity_id;
            }
        }
        $unlinked = $rows->reject(fn ($r) => in_array((int) $r->id, $linked, true))
            ->map(fn ($r) => ['id' => (int) $r->id, 'lang' => (int) $r->language_id === $en ? 'en' : 'vi'])->values()->all();

        return ['pairs' => $pairs, 'unlinked' => $unlinked];
    }

    private function compareFields(string $type, array $pair, string $table, array $fields): array
    {
        $rows = ['en' => DB::table($table)->find($pair['en']), 'vi' => DB::table($table)->find($pair['vi'])];
        foreach ($fields as $field) {
            $a = $rows['en']->{$field} ?? null;
            $b = $rows['vi']->{$field} ?? null;
            if ((is_numeric($a) && is_numeric($b)) ? (float) $a !== (float) $b : $a !== $b) {
                $this->add($type, $pair, $field, $a, $b);
            }
        }

        return $rows;
    }

    private function add(string $type, array $pair, string $field, $en, $vi): void
    {
        [$impact, $verify] = self::IMPACT[$field] ?? ['Unclassified', 'Owner'];
        $this->differences[] = [
            'entity_type' => $type,
            'group_key' => $pair['group'] ?? null,
            'en_id' => $pair['en'] ?? null,
            'vi_id' => $pair['vi'] ?? null,
            'field' => $field,
            'en_value' => $en,
            'vi_value' => $vi,
            'business_impact' => $impact,
            'verify_with' => $verify,
            'decision' => 'owner',
        ];
    }
}
