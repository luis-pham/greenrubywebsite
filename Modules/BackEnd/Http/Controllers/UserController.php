<?php
namespace Modules\BackEnd\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\MessageBag;
use Modules\BackEnd\Http\Requests\UserStoreRequest;
use Modules\BackEnd\Http\Requests\UserUpdateRequest;
use Modules\BackEnd\Helpers\Logging;
use Modules\BackEnd\Helpers\Utilities;
use Modules\BackEnd\Services\AdUserService;
use Modules\BackEnd\Services\AdRoleService;

class UserController extends Controller
{
    private $baseView = 'backend::user.';
    
    public function index(Request $request)
    {
        $title = 'Danh sách người dùng';

        \SEO::setTitle($title);

        $listStatus = $this->getUserStatus();
        $listRole = AdRoleService::getAll()->pluck('name', 'id');

        $param = [];
        if ($request->keyword) {
            $param['keyword'] = $request->keyword;
        }
        if ($request->role_id) {
            $param['role_id'] = $request->role_id;
        }
        if ($request->status || $request->status === (string) config('backend.userStatus.unactive')) {
            $param['status'] = $request->status;
        }
        $list = AdUserService::getPaging($param);

        Logging::logInfo('Xem danh sách người dùng.');

        return view($this->baseView . __FUNCTION__, compact('title', 'listRole', 'listStatus', 'list'));
    }

    public function create()
    {
        $title = 'Thêm người dùng';

        $listStatus = $this->getUserStatus();
        $listTheme = Utilities::getUserTheme();
        $listRole = AdRoleService::getAll()->pluck('name', 'id');

        \SEO::setTitle($title);

        return view($this->baseView . __FUNCTION__, compact('title', 'listStatus', 'listTheme', 'listRole'));
    }

    public function store(UserStoreRequest $request)
    {
        try {
            $data = $request->only(['username', 'password', 'fullname', 'email', 'avatar', 'cover', 'theme']);
            $data['password'] = \Hash::make($data['password']);
            $data['status'] = config('backend.userStatus.unactive');

            $dataDetail = [];
            if (array_key_exists('role_id', $data)) {
                for ($i = 0; $i < count($data['role_id']); $i++) {
                    $dataDetail[] = ['role_id' => $data['role_id'][$i]];
                }
            }

            $id = AdUserService::create($data, $dataDetail);

            $lastUrl = $request->get('lastUrl');
            $route = route('backend.user.show', ['id' => $id, 'lastUrl' => $lastUrl]);

            Logging::logInfo('Thêm người dùng thành công.', 'id = ' . $id);

            return redirect($route)->with('flash-message', 'Thêm người dùng thành công!');
        } catch (\Exception $e) {
            Logging::logError('Thêm người dùng lỗi.', 'Exception = ' . $e->getMessage());

            return redirect()->back()->withErrors('Thêm người dùng lỗi!');
        }
    }
    
    public function show($id)
    {
        $title = 'Chi tiết người dùng';

        \SEO::setTitle($title);

        $listRole = AdRoleService::getAll()->pluck('name', 'id');

        $obj = AdUserService::find($id);
        if (!$obj) {
            return abort(404);
        }

        Logging::logInfo('Xem chi tiết người dùng.', 'id = ' . $id);

        return view($this->baseView . __FUNCTION__, compact('title', 'listRole', 'obj'));
    }

    public function edit($id)
    {
        $title = 'Sửa người dùng';

        \SEO::setTitle($title);

        $obj = AdUserService::find($id);
        if (!$obj) {
            return abort(404);
        }

        $listStatus = $this->getUserStatus();
        $listTheme = Utilities::getUserTheme();
        $listRole = AdRoleService::getAll()->pluck('name', 'id');

        return view($this->baseView . __FUNCTION__, compact('title', 'obj', 'listStatus', 'listTheme', 'listRole'));
    }

    public function update(UserUpdateRequest $request, $id)
    {
        $obj = AdUserService::find($id);
        if (!$obj) {
            return abort(404);
        }

        try {
            $data = $request->all();
            $data['id'] = $id;
            if (!$obj->provider) {
                if (!$data['password']) {
                    unset($data['password']);
                } else {
                    $data['password'] = \Hash::make($data['password']);
                }
            }
            if ($id == \Auth::user()->id || $id == config('backend.adUserAdmin')) {
                $data['status'] = $obj->status;
            }

            $dataDetail = [];
            if ($id != config('backend.adUserAdmin')) {
                if (array_key_exists('role_id', $data)) {
                    for ($i = 0; $i < count($data['role_id']); $i++) {
                        $dataDetail[] = ['role_id' => $data['role_id'][$i]];
                    }
                }
            }

            AdUserService::update($data, $dataDetail);

            $lastUrl = $request->get('lastUrl');
            $route = route('backend.user.show', ['id' => $id, 'lastUrl' => $lastUrl]);

            Logging::logInfo('Sửa người dùng thành công.', 'id = ' . $id);

            return redirect($route)->with('flash-message', 'Sửa người dùng thành công!');
        } catch (\Exception $e) {
            Logging::logError('Sửa người dùng lỗi.', 'id = ' . $id . '. Exception = ' . $e->getMessage());

            return redirect()->back()->withErrors('Sửa người dùng lỗi!');
        }
    }

    public function destroy(Request $request)
    {
        $data = $request->all();
        try
        {
            if (!$data['id']) {
                throw new \Exception('Parameter invalid.');
            }

            if (is_array($data['id'])) {
                if (in_array(config('backend.adUserAdmin'), $data['id'])) {
                    throw new \Exception('Can\'t delete user admin.');
                }
    
                if (in_array(\Auth::user()->id, $data['id'])) {
                    throw new \Exception('Can\'t delete yourself.');
                }
            } else {
                if ($data['id'] == config('backend.adUserAdmin')) {
                    throw new \Exception('Can\'t delete user admin.');
                }
    
                if ($data['id'] == \Auth::user()->id) {
                    throw new \Exception('Can\'t delete yourself.');
                }
            }

            AdUserService::delete($data['id']);
            
            Session::flash('flash-message', 'Xóa người dùng thành công!');
            Logging::logInfo('Xóa người dùng thành công.', 'id = ' . json_encode($data['id'], JSON_UNESCAPED_UNICODE));
            
            return response()->json(['msg' => 'success']);
        }
        catch (\Exception $e)
        {
            Session::flash('errors', new MessageBag(['Xóa người dùng lỗi!']));
            Logging::logError('Xóa người dùng lỗi.', 'id = ' . json_encode($data['id'], JSON_UNESCAPED_UNICODE) . '. Exception = ' . $e->getMessage());
            
            return response()->json([
                'msg' => 'fail',
                'err' => $e->getMessage()
            ]);
        }
    }

    public function info($id)
    {
        $title = 'Thông tin người dùng';

        \SEO::setTitle($title);

        $obj = AdUserService::find($id);
        if (!$obj) {
            return abort(404);
        }

        Logging::logInfo('Xem thông tin người dùng.', 'id = ' . $obj->id);

        return view($this->baseView . __FUNCTION__, compact('title', 'obj'));
    }

    private function getUserStatus()
    {
        $status = config('backend.userStatus');
        return [
            $status['actived'] => 'Hoạt động',
            $status['unactive'] => 'Chưa kích hoạt',
            $status['locked'] => 'Đã khóa'
        ];
    }
}
