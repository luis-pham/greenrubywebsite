<?php

namespace App\Http\Controllers\Api\Editor;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Modules\BackEnd\Entities\AppFile;
use Modules\BackEnd\Helpers\Logging;
use Modules\BackEnd\Helpers\Utilities;
use Modules\BackEnd\Services\AppFileService;

/**
 * Media library for the writing app: the same app_file rows and /upload/Y/m/d folders as the CMS
 * file module. The alt text of an image is stored in app_file.description.
 */
class MediaController extends Controller
{
    private const IMAGE = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    private const VIDEO = ['mp4', 'webm'];

    public function index(Request $request)
    {
        $q = AppFile::query();
        $type = $request->query('type');
        if ($type === 'image' || $type === 'video') {
            $q->whereIn('extension', $type === 'image' ? array_merge(self::IMAGE, array_map('strtoupper', self::IMAGE)) : self::VIDEO);
        }
        if ($kw = trim((string) $request->query('q'))) {
            $q->where(fn ($w) => $w->where('name', 'like', "%$kw%")->orWhere('description', 'like', "%$kw%"));
        }
        $perPage = 48;
        $page = max(1, (int) $request->query('page', 1));
        $total = (clone $q)->count();
        $rows = $q->orderByDesc('id')->offset(($page - 1) * $perPage)->limit($perPage)->get();

        return response()->json([
            'items' => $rows->map(fn ($f) => self::item($f))->all(),
            'total' => $total,
            'page' => $page,
            'lastPage' => max(1, (int) ceil($total / $perPage)),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:' . (64 * 1024) . '|mimes:' . implode(',', array_merge(self::IMAGE, self::VIDEO)),
            'alt' => 'nullable|string|max:500',
        ]);
        $file = $request->file('file');
        $ext = strtolower($file->getClientOriginalExtension());
        $info = Utilities::getUploadFileInfo($file->getClientOriginalName());
        $size = $file->getSize();
        $file->move($info['uploadPath'], $info['fileName']);

        $data = [
            'name' => $info['fileName'],
            'link' => $info['filePath'],
            'size' => $size,
            'extension' => $ext,
            'description' => $request->input('alt') ?: null,
            'is_360' => false,
        ];
        if (in_array($ext, self::VIDEO, true)) {
            $data['thumbnail'] = self::videoPoster($info);
        }
        $id = AppFileService::create($data);
        Logging::logInfo('Editor: tải file lên.', 'id = ' . $id);

        return response()->json(['item' => self::item(AppFile::find($id))], 201);
    }

    public function update(Request $request, int $id)
    {
        $in = $request->validate(['alt' => 'nullable|string|max:500']);
        $f = AppFile::find($id);
        abort_if(!$f, 404);
        $f->description = $in['alt'] ?: null;
        $f->save();

        return response()->json(['item' => self::item($f)]);
    }

    /** First-second frame as the poster, like the CMS file module (ffmpeg); null when it fails. */
    private static function videoPoster(array $info): ?string
    {
        try {
            $thumb = Utilities::getUploadFileInfo(pathinfo($info['fileName'], PATHINFO_FILENAME) . '.jpg');
            $cmd = sprintf('%s -y -i %s -ss 00:00:01 -vframes 1 -vf "scale=1280:-2" -q:v 4 %s 2>&1',
                env('FFMPEG_PATH') ?: 'ffmpeg',
                escapeshellarg($info['uploadPath'] . '/' . $info['fileName']),
                escapeshellarg($thumb['uploadPath'] . '/' . $thumb['fileName']));
            exec($cmd, $output, $code);

            return file_exists($thumb['uploadPath'] . '/' . $thumb['fileName']) ? $thumb['filePath'] : null;
        } catch (\Throwable $e) {
            Logging::logError('Editor: tạo poster video lỗi.', $e->getMessage());

            return null;
        }
    }

    private static function item(AppFile $f): array
    {
        $prefix = config('backend.uploadPath');
        $ext = strtolower((string) $f->extension);

        return [
            'id' => (int) $f->id,
            'name' => $f->name,
            // Public paths as the site uses them: /upload/2026/10/20/name.jpg
            'url' => Str::startsWith($f->link, $prefix . '/') ? $f->link : $prefix . $f->link,
            'poster' => $f->thumbnail ? (Str::startsWith($f->thumbnail, $prefix . '/') ? $f->thumbnail : $prefix . $f->thumbnail) : null,
            'type' => in_array($ext, self::VIDEO, true) ? 'video' : (in_array($ext, self::IMAGE, true) ? 'image' : 'file'),
            'extension' => $ext,
            'size' => (int) $f->size,
            'alt' => $f->description,
            'createdAt' => $f->created_at ? (string) $f->created_at : null,
        ];
    }
}
