<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * After an admin changes content, tells the Next.js frontend which cached data to refresh
 * (POST {FRONTEND_REVALIDATE_URL} {secret, tags}). Tags must match the cacheTag() calls in
 * greenruby-web/src/lib/api/content.ts.
 *
 * Works at the route level rather than on model events because many CMS writes go through
 * DB::table() and pivot sync(), which don't fire Eloquent events.
 */
class RevalidateFrontend
{
    /** Admin route name (without "backend." and "|multi-language") prefix => tags. */
    protected const TAGS = [
        'amenity' => ['cabins', 'cruises'],
        'article' => ['articles'],
        'cabin' => ['cabins', 'cruises'],
        'category' => ['articles'],
        'config' => ['site'],
        'cruise' => ['cruises', 'cabins', 'itineraries'],
        'exp-activity' => ['experiences', 'itineraries', 'cruises'],
        'faq' => ['faqs'],
        'file' => ['content'],
        'group' => ['cabins', 'experiences', 'services', 'faqs'],
        'itinerary' => ['itineraries', 'cruises'],
        'menuFrontEnd' => ['site'],
        'page-config' => ['pages'],
        'service' => ['services', 'itineraries', 'cruises'],
        'testimonial' => ['testimonials'],
    ];

    /** Routes whose {id} identifies one entity => tag prefix for that entity. */
    protected const ID_TAGS = [
        'cabin' => 'cabin',
        'cruise' => 'cruise',
        'itinerary' => 'itinerary',
        'article' => 'article',
        'exp-activity' => 'experience',
    ];

    public function handle(Request $request, Closure $next)
    {
        return $next($request);
    }

    /** Runs after the response has been sent, so admin saves are never slowed down. */
    public function terminate(Request $request, $response): void
    {
        $url = config('services.frontend_revalidate.url');
        $secret = config('services.frontend_revalidate.secret');
        if (!$url || !$secret || $request->isMethodSafe() || $response->getStatusCode() >= 400) {
            return;
        }

        $tags = $this->tagsFor($request);
        if ($tags === []) {
            return;
        }

        try {
            $result = Http::timeout(3)->acceptJson()->post($url, ['secret' => $secret, 'tags' => $tags]);
            if (!$result->successful()) {
                Log::warning('Frontend revalidate failed', ['status' => $result->status(), 'tags' => $tags]);
            }
        } catch (\Throwable $e) {
            Log::warning('Frontend revalidate unreachable', ['error' => $e->getMessage(), 'tags' => $tags]);
        }
    }

    /** @return string[] */
    public function tagsFor(Request $request): array
    {
        $name = (string) optional($request->route())->getName();
        if (!str_starts_with($name, 'backend.')) {
            return [];
        }
        $name = explode('|', substr($name, strlen('backend.')))[0];
        $entity = explode('.', $name)[0];
        $tags = self::TAGS[$entity] ?? [];
        if ($tags === []) {
            return [];
        }

        if ($entity === 'page-config' && ($code = $request->route('pageCode'))) {
            $tags[] = 'page:' . $code;
        }
        $id = $request->route('id');
        if (isset(self::ID_TAGS[$entity]) && is_numeric($id)) {
            $tags[] = self::ID_TAGS[$entity] . ':' . $id;
        }

        return array_values(array_unique($tags));
    }
}
