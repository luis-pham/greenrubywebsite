<?php

namespace App\Http\Controllers\Api\Editor;

use App\Http\Middleware\EditorAuth;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\BackEnd\Entities\AdUser;
use Modules\BackEnd\Helpers\Logging;
use Symfony\Component\HttpFoundation\Cookie;

/** Login for the writing app with the CMS admin accounts (same username/password as /admincp). */
class AuthController extends Controller
{
    public function login(Request $request)
    {
        $data = $request->validate([
            'username' => 'required|string|max:255',
            'password' => 'required|string|max:255',
        ]);

        // Same provider and hasher as the /admincp login form.
        $guard = Auth::guard('admin');
        $user = $guard->validate($data) ? $guard->getLastAttempted() : null;
        if (!$user) {
            Logging::logError('Editor: đăng nhập sai.', 'username = ' . $data['username']);

            return response()->json(['message' => 'Sai tên đăng nhập hoặc mật khẩu.'], 422);
        }
        if ((int) $user->status !== (int) config('backend.userStatus.actived')) {
            return response()->json(['message' => 'Tài khoản chưa kích hoạt hoặc đang bị khóa.'], 422);
        }

        $token = Str::random(64);
        DB::table('editor_tokens')->insert([
            'user_id' => $user->id,
            'token_hash' => hash('sha256', $token),
            'expires_at' => now()->addDays(EditorAuth::TTL_DAYS),
            'last_used_at' => now(),
            'created_at' => now(),
        ]);
        DB::table('editor_tokens')->where('expires_at', '<', now())->delete();

        Auth::shouldUse('admin');
        Auth::guard('admin')->setUser($user);
        Logging::logInfo('Editor: đăng nhập thành công.', 'username = ' . $user->username);

        return response()->json(['user' => self::user($user)])
            ->withCookie(self::cookie($request, $token, EditorAuth::TTL_DAYS * 1440));
    }

    public function logout(Request $request)
    {
        if ($token = EditorAuth::tokenFrom($request)) {
            DB::table('editor_tokens')->where('token_hash', hash('sha256', $token))->delete();
        }

        return response()->json(['ok' => true])->withCookie(self::cookie($request, '', -1));
    }

    public function me()
    {
        $user = Auth::guard('admin')->user();

        return response()->json(['user' => $user
            ? self::user($user)
            : ['id' => 0, 'username' => 'guest', 'fullname' => 'Khách (chỉ xem)', 'guest' => true]]);
    }

    private static function user(AdUser $user): array
    {
        return ['id' => $user->id, 'username' => $user->username, 'fullname' => $user->fullname ?: $user->username];
    }

    private static function cookie(Request $request, string $value, int $minutes): Cookie
    {
        // Host-only cookie (no Domain), so it is never sent to greenrubycruises.com itself.
        return Cookie::create(EditorAuth::COOKIE, $value, $minutes > 0 ? now()->addMinutes($minutes) : 1, '/', null,
            $request->isSecure(), true, true, Cookie::SAMESITE_STRICT);
    }
}
