<?php
namespace Modules\BackEnd\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\BackEnd\Helpers\LanguageUtils;
use Modules\BackEnd\Helpers\Logging;
use Modules\BackEnd\Http\Requests\LoginRequest;

class AuthController extends Controller
{
    private $baseView = 'backend::auth.';

    protected function guard()
    {
        return \Auth::guard('admin');
    }

    protected function safeInternalPath(?string $url): ?string
    {
        if (!$url || !\Str::of($url)->startsWith('/') || \Str::of($url)->startsWith('//')) {
            return null;
        }

        if (str_contains($url, '..')) {
            return null;
        }

        return $url;
    }

    public function index()
    {
        \SEO::setTitle('Đăng nhập');

        return view($this->baseView . __FUNCTION__);
    }

    public function login(LoginRequest $request)
    {
        $data = [
            'username' => $request->input('username'),
            'password' => $request->input('password')
        ];

        $lastUrl = $this->safeInternalPath($request->query('lastUrl'));
        if ($lastUrl) {
            $route = route('backend.auth.login', ['lastUrl' => $lastUrl]);
        } else {
            $route = route('backend.auth.login');
        }

        if (\Auth::guard('admin')->attempt($data)) {
            $obj = \Auth::guard('admin')->getLastAttempted();
            
            if ($obj->status == config('backend.userStatus.unactive')) {
                \Auth::guard('admin')->logout();
                
                Logging::logError('Đăng nhập với tài khoản chưa kích hoạt.', 'username = ' . $data['username']);

                return redirect($route)->withErrors('Tài khoản của bạn chưa được kích hoạt!');
            }
            
            if ($obj->status == config('backend.userStatus.locked')) {
                \Auth::guard('admin')->logout();

                Logging::logError('Đăng nhập với tài khoản đã khóa.', 'username = ' . $data['username']);

                return redirect($route)->withErrors('Tài khoản của bạn hiện đang tạm khóa!');
            }
            
            $language = LanguageUtils::getCurrentLanguage();
            LanguageUtils::setCurrentLanguage($language);

            Logging::logInfo('Đăng nhập thành công.', 'username = ' . $data['username']);

            if ($lastUrl) {
                return redirect()->to($lastUrl);
            }

            return redirect()->route('backend.index');
        }

        Logging::logError('Đăng nhập sai mật khẩu.', 'username = ' . $data['username']);

        return redirect($route)->withErrors('Tên đăng nhập hoặc Mật khẩu không đúng!');
    }

    public function logout()
    {
        $userId = \Auth::guard('admin')->user()->id;
        \Auth::guard('admin')->logout();
        LanguageUtils::clearCurrentLanguage();
        Logging::logInfo('Đăng xuất thành công.', 'id = ' . $userId);
        return redirect()->route('backend.auth.login');
    }
}
