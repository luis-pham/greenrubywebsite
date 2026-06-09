<?php
namespace Modules\BackEnd\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\BackEnd\Helpers\Utilities;
use Modules\BackEnd\Services\AdLoggingService;
use Modules\BackEnd\Services\AdUserService;

class LoggingController extends Controller
{
    private $baseView = 'backend::logging.';
    
    public function index(Request $request)
    {
        $title = 'Danh sách lịch sử hoạt động';

        \SEO::setTitle($title);

        $listUser = AdUserService::getAll()->sortBy('fullname');
        $listType = [
            config('backend.logType.info') => 'Thông tin',
            config('backend.logType.error') => 'Lỗi',
            config('backend.logType.system') => 'Hệ thống'
        ];

        $param = [];
        if ($request->keyword) {
            $param['keyword'] = $request->keyword;
        }
        if ($request->from_date) {
            $param['from_date'] = Utilities::parseDateOnly($request->from_date);
        }
        if ($request->to_date) {
            $param['to_date'] = Utilities::parseDateOnly($request->to_date);
            $param['to_date'] = date('Y-m-d', strtotime('+1 day', strtotime($param['to_date'])));
        }
        if ($request->user_id) {
            $param['user_id'] = $request->user_id;
        }
        if ($request->type) {
            $param['type'] = $request->type;
        }
        $list = AdLoggingService::getPaging($param);

        return view($this->baseView . __FUNCTION__, compact('title', 'listUser', 'listType', 'list'));
    }
    
    public function show($id)
    {
        $title = 'Chi tiết lịch sử hoạt động';

        \SEO::setTitle($title);

        $obj = AdLoggingService::findJoin($id);
        if (!$obj) {
            return abort(404);
        }

        return view($this->baseView . __FUNCTION__, compact('title', 'obj'));
    }
}
