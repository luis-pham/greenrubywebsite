<?php
namespace Modules\BackEnd\Http\Controllers;

use App\Services\Content\SlugService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\BackEnd\Helpers\Logging;

/** The admin's explicit slug change (phase 1E-c): a thin wrapper around SlugService (= `php artisan content:slug`). */
class SlugController extends Controller
{
    public function change(Request $request)
    {
        $type = (string) $request->input('type');
        $id = (int) $request->input('id');
        try {
            $result = SlugService::change($type, $id, trim((string) $request->input('slug')), (string) $request->input('reason'), Auth::id());
        } catch (\RuntimeException $e) {
            return redirect()->back()->withErrors('Không đổi được slug: ' . $e->getMessage());
        }
        Logging::LogInfo('Đổi slug.', "type = $type, id = $id, {$result['from']} → {$result['to']}");

        return redirect()->back()->with('flash-message', $result['changed']
            ? "Đã đổi slug: {$result['from']} → {$result['to']}" . ($result['from'] !== '' ? ' (URL cũ chuyển hướng 308).' : '.')
            : 'Slug không đổi.');
    }
}
