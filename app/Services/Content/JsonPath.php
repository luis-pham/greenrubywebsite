<?php

namespace App\Services\Content;

/**
 * The JSON paths of greenruby-web scripts/i18n/copy/cms-extract.py (phase 2A/2B): `[x]` selects the list item whose
 * "id" is x (its index when the item has no id), `.k` an object key; e.g. "[1372].title",
 * "[73c256f4-…].name". set() changes only that one value and keeps everything else (structure, other items,
 * urls, empty objects), re-encoded in the stored value's own style (escaped slashes / unicode or not).
 */
final class JsonPath
{
    /** @return array<int, array{0: string, 1: string}> [['item', id] | ['key', name]] */
    public static function parse(string $path): array
    {
        if (!preg_match_all('/\[([^\]]*)\]|\.([^.\[]+)/', $path, $m, PREG_SET_ORDER) || implode('', array_column($m, 0)) !== $path) {
            throw new \InvalidArgumentException("Unsupported JSON path '{$path}'.");
        }

        return array_map(fn ($s) => ($s[2] ?? '') !== '' ? ['key', $s[2]] : ['item', $s[1]], $m);
    }

    public static function exists(string $json, string $path): bool
    {
        return self::locate(json_decode($json), self::parse($path))[0];
    }

    public static function get(string $json, string $path): ?string
    {
        [$found, $value] = self::locate(json_decode($json), self::parse($path));

        return $found && is_scalar($value) ? (string) $value : null;
    }

    /** The JSON with the value at $path replaced; the path must exist. */
    public static function set(string $json, string $path, string $value): string
    {
        $data = json_decode($json);
        $segments = self::parse($path);
        $last = array_pop($segments);
        [$found, $parent] = self::locate($data, $segments);
        if (!$found || $last[0] !== 'key' || !is_object($parent) || !property_exists($parent, $last[1])) {
            throw new \RuntimeException("JSON path {$path} not found.");
        }
        $parent->{$last[1]} = $value; // objects are handles: $data is changed in place

        $flags = 0;
        if (!str_contains($json, '\\/')) {
            $flags |= JSON_UNESCAPED_SLASHES;
        }
        if (!preg_match('/\\\\u[0-9a-fA-F]{4}/', $json)) {
            $flags |= JSON_UNESCAPED_UNICODE;
        }

        return json_encode($data, $flags | JSON_PRESERVE_ZERO_FRACTION | JSON_THROW_ON_ERROR);
    }

    /** @return array{0: bool, 1: mixed} */
    private static function locate($node, array $segments): array
    {
        foreach ($segments as [$kind, $name]) {
            if ($kind === 'key') {
                if (!is_object($node) || !property_exists($node, $name)) {
                    return [false, null];
                }
                $node = $node->{$name};
                continue;
            }
            if (!is_array($node)) {
                return [false, null];
            }
            $next = null;
            $hit = false;
            foreach ($node as $i => $item) {
                if (self::itemKey($item, $i) === $name) {
                    [$hit, $next] = [true, $item];
                    break;
                }
            }
            if (!$hit) {
                return [false, null];
            }
            $node = $next;
        }

        return [true, $node];
    }

    /** As Python's f'{item.get("id", i)}' in the extractor. */
    private static function itemKey($item, int $index): string
    {
        if (!is_object($item) || !property_exists($item, 'id')) {
            return (string) $index;
        }
        $id = $item->id;

        return match (true) {
            $id === null => 'None',
            $id === true => 'True',
            $id === false => 'False',
            default => (string) $id,
        };
    }
}
