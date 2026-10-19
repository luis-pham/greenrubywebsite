<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\BackEnd\Entities\AdUser;

/**
 * Authenticates the writing app (write.greenrubycruises.com) by an opaque token sent as the
 * httpOnly cookie "gr_write" (or "Authorization: Bearer"). Writes must also carry "X-Editor: 1":
 * a cross-site form cannot set that header, which is what protects the cookie from CSRF.
 * The admin user becomes the current user, so created_by/updated_by are filled as in the CMS.
 */
class EditorAuth
{
    public const COOKIE = 'gr_write';
    public const TTL_DAYS = 30;

    public function handle(Request $request, Closure $next)
    {
        if (!$request->isMethodSafe() && $request->header('X-Editor') !== '1') {
            return response()->json(['message' => 'Missing X-Editor header'], 403);
        }

        $user = self::userFromToken(self::tokenFrom($request));
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        Auth::shouldUse('admin');
        Auth::guard('admin')->setUser($user);

        $response = $next($request);
        $response->headers->set('Cache-Control', 'no-store');

        return $response;
    }

    public static function tokenFrom(Request $request): ?string
    {
        return $request->bearerToken() ?: ($_COOKIE[self::COOKIE] ?? null);
    }

    public static function userFromToken(?string $token): ?AdUser
    {
        if (!$token || strlen($token) < 40) {
            return null;
        }
        $row = DB::table('editor_tokens')->where('token_hash', hash('sha256', $token))->first();
        if (!$row || now()->greaterThan($row->expires_at)) {
            return null;
        }
        $user = AdUser::find($row->user_id);
        if (!$user || (int) $user->status !== (int) config('backend.userStatus.actived')) {
            return null;
        }

        // Sliding expiry, written at most once an hour.
        if (!$row->last_used_at || now()->diffInMinutes($row->last_used_at) >= 60) {
            DB::table('editor_tokens')->where('id', $row->id)->update([
                'last_used_at' => now(),
                'expires_at' => now()->addDays(self::TTL_DAYS),
            ]);
        }

        return $user;
    }
}
