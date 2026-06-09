<?php
namespace Modules\FrontEnd\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image as ImageFacade;

class ImageController extends Controller
{
    public function thumbnail(Request $request)
    {
        $link = $request->route('link');
        $width = $request->get('w');
        $height = $request->get('h');
        $crop = $request->get('cr');

        try {
            $publicRelativePath = ltrim((string)$link, '/');
            if ($publicRelativePath === '' || str_contains($publicRelativePath, '..')) {
                return abort(404);
            }

            $filePath = public_path($publicRelativePath);
            if (!File::exists($filePath) || !is_file($filePath)) {
                return abort(404);
            }

            $sourceExt = strtolower((string) pathinfo($filePath, PATHINFO_EXTENSION));
            if ($sourceExt === 'svg') {
                return response()->file($filePath, [
                    'Content-Type' => 'image/svg+xml',
                ]);
            }

            $quality = (int) env('IMAGE_PROXY_QUALITY', 90);
            $quality = max(1, min(100, $quality));
            $outputFormat = 'webp';
            $crFlag = $crop ? 1 : 0;
            $cacheKey = sha1(
                $publicRelativePath . '|' . (string)$width . '|' . (string)$height . '|' . (string)$crFlag . '|' . $outputFormat . '|' . (string)$quality
            );
            $thumbDir = storage_path('app/thumbnail');
            $thumbExt = $outputFormat;
            $thumbPath = $thumbDir . DIRECTORY_SEPARATOR . $cacheKey . '.' . $thumbExt;

            $cacheEnabled = filter_var(env('IMAGE_PROXY_CACHE_ENABLED', false), FILTER_VALIDATE_BOOLEAN);
            if ($cacheEnabled && File::exists($thumbPath) && is_file($thumbPath)) {
                $mimeType = File::mimeType($thumbPath) ?: 'image/webp';
                return response()->file($thumbPath, [
                    'Content-Type' => $mimeType
                ]);
            }

            $image = ImageFacade::make($filePath);
            $width = $width && $width < $image->width() ? $width : null;
            $height = $height && $height < $image->height() ? $height : null;
            
            if (!$crop) {
                if ($width && !$height) {
                    $ratio = $image->height() / $image->width();
                    $height = (int)($width * $ratio);
                } else if (!$width && $height) {
                    $ratio = $image->width() / $image->height();
                    $width = (int)($height * $ratio);
                }
            }
            
            if (!$width || $width > $image->width()) {
                $width = $image->width();
            }
            if (!$height || $height > $image->height()) {
                $height = $image->height();
            }

            $final = $image->fit($width, $height)->encode($outputFormat, $quality);

            if ($cacheEnabled) {
                if (!File::exists($thumbDir)) {
                    File::makeDirectory($thumbDir, 0755, true);
                }
                File::put($thumbPath, $final->encoded);

                return response()->file($thumbPath, [
                    'Content-Type' => 'image/webp',
                ]);
            }

            return response($final->encoded, 200, [
                'Content-Type' => 'image/webp',
            ]);
        } catch (\Exception $e) {
            return abort(404);
        }
    }
}
