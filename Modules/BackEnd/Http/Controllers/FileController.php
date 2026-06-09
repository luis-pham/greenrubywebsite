<?php

namespace Modules\BackEnd\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\MessageBag;
use Modules\BackEnd\Helpers\Logging;
use Modules\BackEnd\Helpers\MediaUtils;
use Modules\BackEnd\Helpers\Utilities;
use Modules\BackEnd\Http\Requests\FileRequest;
use Modules\BackEnd\Services\AppFileService;

class FileController extends Controller
{
    private $baseView = 'backend::file.';

    public function index(Request $request)
    {
        $title = 'Danh sách file';

        \SEO::setTitle($title);

        $listFileType = Utilities::getFileTypeName();

        $param = [];
        if ($request->keyword) {
            $param['keyword'] = $request->keyword;
        }
        if ($request->type) {
            $type = explode(',', $request->type);
            $listExtension = [];
            for ($i = 0; $i < count($type); $i++) {
                $listExtension = array_merge(
                    $listExtension,
                    Utilities::getFileExtensionByType($type[$i])
                );
            }
            $param['extension'] = array_values(array_unique($listExtension));
        }
        if ($request->exclude_id) {
            $param['exclude_id'] = explode(',', $request->exclude_id);
        }
        if ($request->from_date) {
            $param['from_date'] = Utilities::parseDateOnly($request->from_date);
        }
        if ($request->to_date) {
            $param['to_date'] = Utilities::parseDateOnly($request->to_date);
            $param['to_date'] = date('Y-m-d', strtotime('+1 day', strtotime($param['to_date'])));
        }
        $list = AppFileService::getPaging($param);

        Logging::logInfo('Xem danh sách file.');

        return view($this->baseView . __FUNCTION__, compact('title', 'listFileType', 'list'));
    }

    public function store(FileRequest $request)
    {
        $dataSuccess = [];
        $dataError = [];
        if ($request->hasfile('file')) {
            foreach ($request->file('file') as $key => $obj) {
                $fileName = $obj->getClientOriginalName();
                $fileSize = $obj->getSize();
                $fileExtension = $obj->getClientOriginalExtension();
                try {
                    $fileInfo = Utilities::getUploadFileInfo($fileName);
                    $obj->move($fileInfo['uploadPath'], $fileInfo['fileName']);

                    $data = [
                        'name' => $fileInfo['fileName'],
                        'link' => $fileInfo['filePath'],
                        'size' => $fileSize,
                        'extension' => $fileExtension,
                        'is_360' => false
                    ];
                    if (Str::endsWith($fileInfo['filePath'], '.mp4')) {
                        try {
                            $thumbnailFileName = pathinfo($fileName, PATHINFO_FILENAME) . '.jpg';
                            $thumbnailFileInfo = Utilities::getUploadFileInfo($thumbnailFileName);
                            $videoFullPath = $fileInfo['uploadPath'] . '/' . $fileInfo['fileName'];
                            $thumbnailFullPath = $thumbnailFileInfo['uploadPath'] . '/' . $thumbnailFileInfo['fileName'];

                            $ffmpegPath = env('FFMPEG_PATH') ?: 'ffmpeg';
                            $ffprobePath = env('FFPROBE_PATH') ?: 'ffprobe';
                            
                            $videoArg = escapeshellarg($videoFullPath);
                            $thumbArg = escapeshellarg($thumbnailFullPath);
                            $cmd = sprintf('%s -y -i %s -ss 00:00:01 -vframes 1 -vf "scale=210:-1" -q:v 5 %s 2>&1', $ffmpegPath, $videoArg, $thumbArg);
                            $output = [];
                            exec($cmd, $output, $returnCode);

                            if (file_exists($thumbnailFullPath)) {
                                $data['thumbnail'] = $thumbnailFileInfo['filePath'];
                            } else {
                                $errMsg = implode("\n", $output);
                                throw new \Exception("FFmpeg error code " . $returnCode . ":" . $errMsg);
                            }
                        } catch (\Exception $e) {
                            Logging::logError('Tạo thumbnail cho video lỗi.', 'fileName = ' . $fileName . '. Exception = ' . $e->getMessage());
                        }

                        $data['is_360'] = MediaUtils::isVideo360($fileInfo['uploadPath'] . DIRECTORY_SEPARATOR . $fileInfo['fileName'],$ffprobePath);
                    }
                    $id = AppFileService::create($data);

                    $dataSuccess[] = [
                        'idx' => $key,
                        'fileName' => $fileName,
                        'id' => $id
                    ];
                } catch (\Exception $e) {
                    $dataError[] = [
                        'idx' => $key,
                        'fileName' => $fileName,
                        'exception' => $e->getMessage()
                    ];
                }
            }
        }

        if (!empty($dataSuccess)) {
            $id = [];
            for ($i = 0; $i < count($dataSuccess); $i++) {
                $id[] = $dataSuccess[$i]['id'];
            }
            Logging::logInfo('Tải file lên thành công.', 'id = ' . json_encode($id, JSON_UNESCAPED_UNICODE));
        }

        if (!empty($dataError)) {
            Logging::logError('Tải file lên lỗi.', json_encode($dataError, JSON_UNESCAPED_UNICODE));
        }

        // $statusCode = empty($dataError)
        //                 ? Response::HTTP_OK
        //                 : Response::HTTP_INTERNAL_SERVER_ERROR;

        return response()->json([
            'msg' => empty($dataError) ? 'success' : 'fail',
            'data' => [
                'success' => $dataSuccess,
                'error' => $dataError
            ]
        ]);
    }

    public function destroy(Request $request)
    {
        $data = $request->all();
        try {
            AppFileService::delete($data['id']);

            Session::flash('flash-message', 'Xóa file thành công!');
            Logging::logInfo('Xóa file thành công.', 'id = ' . json_encode($data['id'], JSON_UNESCAPED_UNICODE));

            return response()->json(['msg' => 'success']);
        } catch (\Exception $e) {
            Session::flash('errors', new MessageBag(['Xóa file lỗi!']));
            Logging::logError('Xóa file lỗi.', 'id = ' . json_encode($data['id'], JSON_UNESCAPED_UNICODE) . '. Exception = ' . $e->getMessage());

            return response()->json([
                'msg' => 'fail',
                'err' => $e->getMessage()
            ]);
        }
    }
}
