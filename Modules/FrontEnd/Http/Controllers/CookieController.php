<?php
namespace Modules\FrontEnd\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class CookieController extends Controller
{
    public function consent(Request $request)
    {
        $request->validate([
            'consent' => 'required|in:accepted,declined'
        ]);

        $cookie = cookie(
            name: 'cookie_consent',
            value: $request->consent,
            minutes: 60 * 24 * 365,
            secure: true,
            httpOnly: false
        );

        return response()->json([
            'msg' => 'success'
        ])->withCookie($cookie);
    }
}