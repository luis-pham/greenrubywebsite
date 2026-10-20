<?php

namespace App\Services\Ai;

/**
 * External link policy for AI drafts, read from resources/ai/link-policy.txt: official sources are always
 * allowed; competitors (cruise operators, resellers, travel agencies, OTAs, review platforms) are blocked by
 * domain, by name under any TLD, or by a word in the host name.
 */
class LinkPolicy
{
    private array $allow = [];
    private array $tokens = [];
    private array $domains = [];
    private array $names = [];

    public function __construct(?string $file = null)
    {
        foreach (file($file ?? resource_path('ai/link-policy.txt'), FILE_IGNORE_NEW_LINES) as $line) {
            $line = strtolower(trim(preg_replace('/#.*$/', '', $line)));
            if ($line === '') {
                continue;
            }
            if (str_starts_with($line, 'allow:')) {
                $this->allow[] = substr($line, 6);
            } elseif (str_starts_with($line, 'token:')) {
                $this->tokens[] = substr($line, 6);
            } elseif (str_ends_with($line, '.*')) {
                $this->names[] = substr($line, 0, -2);
            } else {
                $this->domains[] = $line;
            }
        }
    }

    public function isBlocked(string $url): bool
    {
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        $host = preg_replace('/^www\./', '', $host);
        if ($host === '') {
            return false;
        }
        foreach ($this->allow as $d) {
            if ($host === $d || str_ends_with($host, '.' . $d)) {
                return false;
            }
        }
        foreach ($this->domains as $d) {
            if ($host === $d || str_ends_with($host, '.' . $d)) {
                return true;
            }
        }
        $labels = explode('.', $host);
        foreach ($this->names as $n) {
            // "agoda.*" matches agoda.com, agoda.co.uk and sub.agoda.vn.
            foreach ($labels as $i => $label) {
                if ($label === $n && $i < count($labels) - 1) {
                    return true;
                }
            }
        }
        $name = implode('.', array_slice($labels, 0, max(1, count($labels) - 1)));
        foreach ($this->tokens as $t) {
            if (str_contains($name, $t)) {
                return true;
            }
        }

        return false;
    }

    /** Explicit domains for the web search tool's blocked_domains (plain hostnames only). */
    public function searchBlocklist(int $max = 200): array
    {
        return array_slice($this->domains, 0, $max);
    }
}
