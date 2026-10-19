<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Tells the Next.js frontend to refresh cached data (POST {FRONTEND_REVALIDATE_URL} {secret, tags}).
 * Same contract as RevalidateFrontend, for writes that don't go through the admin routes.
 */
class FrontendRevalidator
{
    /** Sends the request after the response has gone out, so saves are never slowed down. */
    public static function later(array $tags): void
    {
        app()->terminating(fn () => self::send($tags));
    }

    public static function send(array $tags): bool
    {
        $url = config('services.frontend_revalidate.url');
        $secret = config('services.frontend_revalidate.secret');
        $tags = array_values(array_unique(array_filter($tags)));
        if (!$url || !$secret || $tags === []) {
            return false;
        }

        try {
            $result = Http::timeout(3)->acceptJson()->post($url, ['secret' => $secret, 'tags' => $tags]);
            if (!$result->successful()) {
                Log::warning('Frontend revalidate failed', ['status' => $result->status(), 'tags' => $tags]);
            }

            return $result->successful();
        } catch (\Throwable $e) {
            Log::warning('Frontend revalidate unreachable', ['error' => $e->getMessage(), 'tags' => $tags]);

            return false;
        }
    }
}
