<?php

namespace Modules\BackEnd\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\BackEnd\Http\Requests\PersonalChangePasswordUpdateRequest;
use Modules\BackEnd\Http\Requests\PersonalUpdateRequest;
use Modules\BackEnd\Helpers\Logging;
use Modules\BackEnd\Helpers\Utilities;
use Modules\BackEnd\Services\AdUserService;

class PersonalController extends Controller
{
    private $baseView = 'backend::personal.';

    public function edit()
    {
        $title = 'Thông tin cá nhân';

        \SEO::setTitle($title);

        $obj = AdUserService::find(\Auth::user()->id);
        if (!$obj) {
            return abort(404);
        }

        $listTheme = Utilities::getUserTheme();

        return view($this->baseView . __FUNCTION__, compact('title', 'obj', 'listTheme'));
    }

    public function update(PersonalUpdateRequest $request)
    {
        $id = \Auth::user()->id;

        try {
            $data = $request->all();
            $notUseAvatar = array_key_exists('not_use_avatar', $data);
            $data = [
                'id' => $id,
                'fullname' => $data['fullname'],
                'theme' => $data['theme']
            ];
            if ($notUseAvatar) {
                $data['avatar'] = null;
                $obj = AdUserService::find($id);
                if ($obj && $obj->avatar) {
                    $file = Utilities::getFileLink($obj->avatar);
                    @unlink(ltrim($file, '/'));
                }
            } else if ($request->hasFile('avatar')) {
                $file = $request->avatar;
                $fileName = $file->getClientOriginalName();
                $fileInfo = Utilities::getUploadFileInfo($fileName);
                $file->move($fileInfo['uploadPath'], $fileInfo['fileName']);

                $data['avatar'] = $fileInfo['filePath'];
                $obj = AdUserService::find($id);
                if ($obj && $obj->avatar) {
                    $file = Utilities::getFileLink($obj->avatar);
                    @unlink(ltrim($file, '/'));
                }
            }
            AdUserService::updatePersonal($data);

            $route = route('backend.personal.edit');

            Logging::logInfo('Sửa thông tin cá nhân thành công.', 'id = ' . $id);

            return redirect($route)->with('flash-message', 'Lưu thông tin cá nhân thành công!');
        } catch (\Exception $e) {
            Logging::logError('Sửa thông tin cá nhân lỗi.', 'id = ' . $id . '. Exception = ' . $e->getMessage());

            return redirect()->back()->withErrors('Lưu thông tin cá nhân lỗi!');
        }
    }

    public function changePasswordEdit()
    {
        $title = 'Đổi mật khẩu';

        \SEO::setTitle($title);

        return view($this->baseView . __FUNCTION__, compact('title'));
    }

    public function changePasswordUpdate(PersonalChangePasswordUpdateRequest $request)
    {
        $id = \Auth::user()->id;

        try {
            $data = $request->all();
            AdUserService::updatePassword([
                'id' => $id,
                'password' => \Hash::make($data['newPassword'])
            ]);

            $route = route('backend.personal.changePasswordEdit');

            Logging::logInfo('Sửa mật khẩu thành công.', 'id = ' . $id);

            return redirect($route)->with('flash-message', 'Lưu mật khẩu thành công!');
        } catch (\Exception $e) {
            Logging::logError('Sửa mật khẩu lỗi.', 'id = ' . $id . '. Exception = ' . $e->getMessage());

            return redirect()->back()->withErrors('Lưu mật khẩu lỗi!');
        }
    }

    public function updateTheme(Request $request)
    {
        $id = \Auth::user()->id;

        try {
            $data = $request->all();
            AdUserService::updatePersonal([
                'id' => $id,
                'theme' => $data['theme']
            ]);

            Logging::logInfo('Sửa giao diện (Thông tin cá nhân) thành công.', 'id = ' . $id);

            return response()->json(['msg' => 'success']);
        } catch (\Exception $e) {
            Logging::logError('Sửa giao diện (Thông tin cá nhân) lỗi.', 'id = ' . $id . '. Exception = ' . $e->getMessage());

            return response()->json([
                'msg' => 'fail',
            ]);
        }
    }
}
