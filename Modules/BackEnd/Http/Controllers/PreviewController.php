<?php
namespace Modules\BackEnd\Http\Controllers;

use App\Services\Preview\Preview;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\BackEnd\Helpers\Logging;

/**
 * "Xem trước" (phase 1E-d). Issues a single-use preview token for one entity and hands it to the website's
 * /_draft/enter in a POST body (an auto-submitting form): the token never appears in a URL, an access log, a
 * referrer or analytics. The page itself is no-store and noindex.
 */
class PreviewController extends Controller
{
    public function start(Request $request)
    {
        $type = (string) $request->input('type');
        $id = (int) $request->input('id');
        $entry = (string) config('i18n.preview.entry_url');
        if (!preg_match('#^https?://[^/]+/_draft/enter$#', $entry)) {
            return redirect()->back()->withErrors('Chưa cấu hình PREVIEW_ENTRY_URL (…/_draft/enter).');
        }
        try {
            $token = Preview::issue((int) Auth::id(), $type, $id);
        } catch (\RuntimeException $e) {
            return redirect()->back()->withErrors('Không xem trước được: ' . $e->getMessage());
        }
        Logging::LogInfo('Mở xem trước.', "type = $type, id = $id"); // the token itself is never logged

        $html = '<!doctype html><html><head><meta charset="utf-8"><meta name="robots" content="noindex,nofollow"><title>Preview</title></head>'
            . '<body><form id="p" method="post" action="' . e($entry) . '"><input type="hidden" name="token" value="' . e($token) . '">'
            . '<noscript><button type="submit">Mở xem trước</button></noscript></form>'
            . '<script>document.getElementById("p").submit()</script></body></html>';

        return response($html)->withHeaders([
            'Cache-Control' => 'no-store, private',
            'Referrer-Policy' => 'no-referrer',
            'X-Robots-Tag' => 'noindex, nofollow',
        ]);
    }
}
