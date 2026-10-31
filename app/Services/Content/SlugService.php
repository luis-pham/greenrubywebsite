<?php

namespace App\Services\Content;

use App\Support\FrontendRevalidator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * The deliberate URL change (owner decision #4: renaming never changes a URL; editors change a slug explicitly; old
 * URLs redirect with 308). One implementation for `php artisan content:slug` and the admin slug action (phase 1E-c):
 * SlugValidator checks the new slug, app_slug_history keeps the old one (the existing 308 redirects read it), and
 * the website is expired at once. No other validation or redirect mechanism exists.
 */
final class SlugService
{
    public const MESSAGES = [
        'slug_empty' => 'Slug không được trống.',
        'slug_too_long' => 'Slug dài quá 160 ký tự.',
        'slug_format' => 'Slug chỉ gồm chữ thường a–z, số và dấu gạch ngang (ASCII), ví dụ green-ruby-1.',
        'slug_reserved' => 'Slug trùng một đường dẫn dành riêng của website.',
        'slug_taken' => 'Slug đã được dùng (hoặc là slug cũ còn chuyển hướng) trong cùng phạm vi.',
    ];

    /** Current slug, or '' when none is stored (intl rows never derive one). */
    public static function current(string $type, object $row): string
    {
        return Slugs::of($row, $type === 'article' ? 'title' : 'name');
    }

    /** Error code for $new, or null when it can be applied. */
    public static function check(string $type, object $row, string $new): ?string
    {
        return SlugValidator::check($type, $new, (array) $row, (int) $row->id);
    }

    /**
     * Applies the change. @return array{changed: bool, from: string, to: string}
     * @throws \RuntimeException with a readable message when refused
     */
    public static function change(string $type, int $id, string $new, string $reason, ?int $actor = null, bool $dryRun = false): array
    {
        $meta = SlugValidator::TYPES[$type] ?? null;
        if (!$meta) {
            throw new \RuntimeException('Type must be one of: ' . implode(', ', array_keys(SlugValidator::TYPES)) . '.');
        }
        $reason = trim($reason);
        if (mb_strlen($reason) < 10) {
            throw new \RuntimeException('Give a reason of at least 10 characters (it is logged).');
        }
        $row = DB::table($meta['table'])->where('id', $id)->first();
        if (!$row) {
            throw new \RuntimeException("{$type} #{$id} not found.");
        }
        $old = self::current($type, $row);
        if ($new === $old) {
            return ['changed' => false, 'from' => $old, 'to' => $new];
        }
        if ($error = self::check($type, $row, $new)) {
            throw new \RuntimeException("Refused: {$error}. " . (self::MESSAGES[$error] ?? ''));
        }
        if ($dryRun) {
            return ['changed' => false, 'from' => $old, 'to' => $new];
        }
        DB::transaction(function () use ($meta, $row, $type, $old, $new, $reason, $actor) {
            if ($old !== '') {
                DB::table('app_slug_history')->insert([
                    'entity_type' => $type, 'entity_id' => $row->id, 'language_id' => $row->language_id,
                    'old_slug' => $old, 'new_slug' => $new, 'reason' => $reason, 'created_at' => now(),
                ]);
            }
            DB::table($meta['table'])->where('id', $row->id)->update(['slug' => $new]);
        });
        Log::notice('Slug changed', ['type' => $type, 'id' => $row->id, 'from' => $old, 'to' => $new, 'reason' => $reason, 'actor' => $actor]);
        EditorialAudit::record($type, (int) $row->id, 'slug_change', $old, $new, $actor, null, $reason);
        // Expire at once: every page linking the old path must move to the new one in one pass.
        FrontendRevalidator::send(['content'], true);

        return ['changed' => true, 'from' => $old, 'to' => $new];
    }

    public static function history(string $type, int $id)
    {
        return DB::table('app_slug_history')->where('entity_type', $type)->where('entity_id', $id)->orderByDesc('id')->get();
    }
}
