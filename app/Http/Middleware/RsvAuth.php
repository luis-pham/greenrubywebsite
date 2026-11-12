<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\BackEnd\Entities\AdUser;

/**
 * Authenticates the reservations desk (rsv.greenruby.cruises) by an opaque token in the httpOnly cookie
 * "gr_rsv" (or "Authorization: Bearer"). Writes must also carry "X-Rsv: 1" (CSRF guard, as X-Editor in
 * EditorAuth). Only the CMS users allowed in config/rsv.php get in. No guest mode: guest data is personal.
 */
class RsvAuth
{
    public const COOKIE = 'gr_rsv';
    public const TTL_DAYS = 14;

    public function handle(Request $request, Closure $next)
    {
        if (!$request->isMethodSafe() && $request->header('X-Rsv') !== '1') {
            return response()->json(['message' => 'Missing X-Rsv header'], 403);
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

    /** May this CMS user open the desk (config rsv.user_ids / rsv.role_ids)? */
    public static function allowed(AdUser $user): bool
    {
        if ((int) $user->status !== (int) config('backend.userStatus.actived')) {
            return false;
        }
        if (in_array((int) $user->id, config('rsv.user_ids', []), true)) {
            return true;
        }
        $roles = config('rsv.role_ids', []);

        return $roles && DB::table('ad_user_role')->where('user_id', $user->id)->whereIn('role_id', $roles)->exists();
    }

    /** @return int[] ids of every CMS user who may open the desk (for the "assigned to" picker). */
    public static function allowedUserIds(): array
    {
        $ids = config('rsv.user_ids', []);
        if ($roles = config('rsv.role_ids', [])) {
            $ids = array_merge($ids, DB::table('ad_user_role')->whereIn('role_id', $roles)->pluck('user_id')->map('intval')->all());
        }

        return AdUser::whereIn('id', array_unique($ids))->where('status', config('backend.userStatus.actived'))->pluck('id')->map('intval')->all();
    }

    public static function userFromToken(?string $token): ?AdUser
    {
        if (!$token || strlen($token) < 40) {
            return null;
        }
        $row = DB::table('rsv_tokens')->where('token_hash', hash('sha256', $token))->first();
        if (!$row || now()->greaterThan($row->expires_at)) {
            return null;
        }
        $user = AdUser::find($row->user_id);
        if (!$user || !self::allowed($user)) {
            return null;
        }

        if (!$row->last_used_at || now()->diffInMinutes($row->last_used_at) >= 60) {
            DB::table('rsv_tokens')->where('id', $row->id)->update([
                'last_used_at' => now(),
                'expires_at' => now()->addDays(self::TTL_DAYS),
            ]);
        }

        return $user;
    }
}
