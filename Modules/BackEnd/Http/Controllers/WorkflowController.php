<?php
namespace Modules\BackEnd\Http\Controllers;

use App\Services\Content\Workflow;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\BackEnd\Helpers\Logging;

/** Workflow buttons of the translation panel (phase 1E-d): a thin wrapper around Workflow::apply. */
class WorkflowController extends Controller
{
    private const RESULTS = [
        'translation_review' => 'Đã gửi duyệt bản dịch.', 'seo_review' => 'Đã duyệt bản dịch; chờ duyệt SEO.',
        'legal_review' => 'Đã duyệt SEO; chờ duyệt pháp lý.', 'approved' => 'Đã duyệt. Có thể xuất bản.',
        'draft' => 'Đã trả về nháp.', 'published' => 'Đã xuất bản (website đã được làm mới).',
        'unpublished' => 'Đã gỡ xuất bản (website đã được làm mới).', 'revision_applied' => 'Đã duyệt và áp dụng bản sửa vào bản đang hiển thị.',
    ];

    public function transition(Request $request)
    {
        $type = (string) $request->input('type');
        $id = (int) $request->input('id');
        $action = (string) $request->input('action');
        try {
            $result = Workflow::apply($type, $id, $action, Auth::id());
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors('Không thực hiện được: ' . $e->getMessage());
        }
        Logging::LogInfo('Quy trình bản dịch.', "type = $type, id = $id, action = $action, result = $result");

        return redirect()->back()->with('flash-message', self::RESULTS[$result] ?? $result);
    }
}
