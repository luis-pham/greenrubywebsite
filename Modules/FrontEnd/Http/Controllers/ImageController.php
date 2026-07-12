<?php
namespace Modules\FrontEnd\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image as ImageFacade;

class ImageController extends Controller
{
    private const THUMBNAIL_CACHE_CONTROL = 'public, max-age=31536000, immutable';

    public function thumbnail(Request $request)
    {
        $link = $request->route('link');
        $reqWidth = $request->get('w') ? (int) $request->get('w') : null;
        $reqHeight = $request->get('h') ? (int) $request->get('h') : null;
        $crop = filter_var($request->get('cr'), FILTER_VALIDATE_BOOLEAN);

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
                    'Cache-Control' => self::THUMBNAIL_CACHE_CONTROL,
                    'X-Content-Type-Options' => 'nosniff',
                ]);
            }

            $defaultQuality = max(1, min(80, (int) env('IMAGE_PROXY_QUALITY', 80)));
            $quality = max(1, min(100, (int) ($request->get('q') ?: $defaultQuality)));
            $outputFormat = 'webp';
            $crFlag = $crop ? 1 : 0;
            $cacheKey = sha1(
                $publicRelativePath . '|' . (string)$reqWidth . '|' . (string)$reqHeight . '|' . (string)$crFlag . '|' . $outputFormat . '|' . (string)$quality
            );
            $thumbDir = storage_path('app/thumbnail');
            $thumbPath = $thumbDir . DIRECTORY_SEPARATOR . $cacheKey . '.' . $outputFormat;

            $cacheEnabled = filter_var(env('IMAGE_PROXY_CACHE_ENABLED', true), FILTER_VALIDATE_BOOLEAN);
            if ($cacheEnabled && File::exists($thumbPath) && is_file($thumbPath)) {
                $mimeType = File::mimeType($thumbPath) ?: 'image/webp';
                return response()->file($thumbPath, [
                    'Content-Type' => $mimeType,
                    'Cache-Control' => self::THUMBNAIL_CACHE_CONTROL,
                    'X-Content-Type-Options' => 'nosniff',
                ]);
            }

            $image = ImageFacade::make($filePath);

            if ($reqWidth || $reqHeight) {
                $noUpscale = function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                };

                if ($crop && $reqWidth && $reqHeight) {
                    $image->fit($reqWidth, $reqHeight, function ($constraint) {
                        $constraint->upsize();
                    });
                } elseif ($reqWidth && $reqHeight) {
                    $image->resize($reqWidth, $reqHeight, $noUpscale);
                } elseif ($reqWidth) {
                    $image->resize($reqWidth, null, $noUpscale);
                } else {
                    $image->resize(null, $reqHeight, $noUpscale);
                }
            }

            $final = $image->encode($outputFormat, $quality);

            if ($cacheEnabled) {
                if (!File::exists($thumbDir)) {
                    File::makeDirectory($thumbDir, 0755, true);
                }
                File::put($thumbPath, $final->encoded);

                return response()->file($thumbPath, [
                    'Content-Type' => 'image/webp',
                    'Cache-Control' => self::THUMBNAIL_CACHE_CONTROL,
                    'X-Content-Type-Options' => 'nosniff',
                ]);
            }

            return response($final->encoded, 200, [
                'Content-Type' => 'image/webp',
                'Cache-Control' => self::THUMBNAIL_CACHE_CONTROL,
                'X-Content-Type-Options' => 'nosniff',
            ]);
        } catch (\Exception $e) {
            return abort(404);
        }
    }
}
