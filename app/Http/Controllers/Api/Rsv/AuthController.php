<?php

namespace App\Http\Controllers\Api\Rsv;

use App\Http\Middleware\RsvAuth;
use App\Services\Rsv\RsvAudit;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\BackEnd\Entities\AdUser;
use Modules\BackEnd\Helpers\Logging;
use Symfony\Component\HttpFoundation\Cookie;

/** Login for the reservations desk with the CMS admin accounts; only users allowed in config/rsv.php. */
class AuthController extends Controller
{
    public function login(Request $request)
    {
        $data = $request->validate([
            'username' => 'required|string|max:255',
            'password' => 'required|string|max:255',
        ]);

        $guard = Auth::guard('admin');
        $user = $guard->validate($data) ? $guard->getLastAttempted() : null;
        if (!$user) {
            Logging::logError('Rsv: đăng nhập sai.', 'username = ' . $data['username']);

            return response()->json(['message' => 'Sai tên đăng nhập hoặc mật khẩu.'], 422);
        }
        if (!RsvAuth::allowed($user)) {
            Logging::logError('Rsv: tài khoản không có quyền.', 'username = ' . $user->username);

            return response()->json(['message' => 'Tài khoản này chưa được cấp quyền vào trang đặt chỗ.'], 403);
        }

        $token = Str::random(64);
        DB::table('rsv_tokens')->insert([
            'user_id' => $user->id,
            'token_hash' => hash('sha256', $token),
            'expires_at' => now()->addDays(RsvAuth::TTL_DAYS),
            'last_used_at' => now(),
            'created_at' => now(),
        ]);
        DB::table('rsv_tokens')->where('expires_at', '<', now())->delete();
        RsvAudit::log('login', null, null, [], (int) $user->id);

        return response()->json(['user' => self::user($user)])
            ->withCookie(self::cookie($request, $token, RsvAuth::TTL_DAYS * 1440));
    }

    public function logout(Request $request)
    {
        if ($token = RsvAuth::tokenFrom($request)) {
            DB::table('rsv_tokens')->where('token_hash', hash('sha256', $token))->delete();
        }

        return response()->json(['ok' => true])->withCookie(self::cookie($request, '', -1));
    }

    public function me()
    {
        return response()->json(['user' => self::user(Auth::guard('admin')->user())]);
    }

    /** CMS users the desk can assign enquiries to. */
    public function users()
    {
        $users = AdUser::whereIn('id', RsvAuth::allowedUserIds())->orderBy('fullname')->get();

        return response()->json(['items' => $users->map(fn (AdUser $u) => self::user($u))->values()]);
    }

    public static function user(AdUser $user): array
    {
        return ['id' => (int) $user->id, 'username' => $user->username, 'fullname' => $user->fullname ?: $user->username];
    }

    private static function cookie(Request $request, string $value, int $minutes): Cookie
    {
        // Host-only, strict: never sent to any other Green Ruby site.
        return Cookie::create(RsvAuth::COOKIE, $value, $minutes > 0 ? now()->addMinutes($minutes) : 1, '/', null,
            $request->isSecure(), true, true, Cookie::SAMESITE_STRICT);
    }
}
