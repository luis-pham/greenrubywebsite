<?php

namespace App\Http\Controllers\Api\Content;

use App\Http\Controllers\Controller;
use App\Http\Middleware\ContentPreview;
use App\Services\Preview\Preview;
use Illuminate\Http\Request;

/**
 * POST /api/v1/content/preview/verify (phase 1E-d): called server-to-server by the website's /_draft/enter with the
 * content key AND X-Preview-Key. Consumes the single-use token and returns the preview session (never logged).
 */
class PreviewController extends Controller
{
    public function verify(Request $request)
    {
        $expected = (string) config('i18n.preview.key');
        if ($expected === '' || !hash_equals($expected, (string) $request->header('X-Preview-Key'))) {
            return ContentPreview::private(response()->json(['error' => 'preview_invalid'], 403));
        }
        $token = $request->input('token');
        if (!is_string($token) || !preg_match('/^[a-f0-9]{64}$/', $token)) {
            return ContentPreview::private(response()->json(['error' => 'preview_token_invalid'], 403));
        }
        try {
            return ContentPreview::private(response()->json(Preview::verify($token)));
        } catch (\RuntimeException $e) {
            return ContentPreview::private(response()->json(['error' => $e->getMessage()], 403));
        }
    }
}
