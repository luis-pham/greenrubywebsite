<?php

namespace Modules\BackEnd\Helpers;

class MediaUtils
{
    static function isVideo360(string $videoPath, string $ffprobePath): bool
    {
        if (!file_exists($videoPath)) {
            return false;
        }

        // Add -show_entries stream_side_data_list to make sure we get detailed side data
        $command = sprintf(
            '%s -v quiet -print_format json -show_streams -show_format -show_entries stream_side_data_list %s',
            escapeshellcmd($ffprobePath),
            escapeshellarg($videoPath)
        );

        $output = shell_exec($command);

        if (empty($output)) {
            return false;
        }

        $data = json_decode($output, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return false;
        }

        // Strategy 1: Check side_data_list for spherical metadata
        foreach ($data['streams'] ?? [] as $stream) {
            if (($stream['codec_type'] ?? '') !== 'video') {
                continue;
            }

            foreach ($stream['side_data_list'] ?? [] as $sideData) {
                $type = $sideData['side_data_type'] ?? '';

                // More tolerant matching
                $lowerType = strtolower($type);

                if (str_contains($lowerType, 'spherical') ||
                    str_contains($lowerType, 'equirectangular') ||
                    str_contains($lowerType, 'mapping')) {   // catches "Spherical Mapping"
                    return true;
                }

                // Also check projection field directly (present in your file)
                $proj = $sideData['projection'] ?? '';
                if (stripos($proj, 'equirectangular') !== false) {
                    return true;
                }
            }
        }

        // Strategy 2: Broader tag search (XMP-style or custom)
        $allTags = array_merge(
            $data['format']['tags'] ?? [],
            ...array_map(fn($s) => $s['tags'] ?? [], $data['streams'] ?? [])
        );

        $keywords = ['spherical', 'equirectangular', 'projectiontype', 'stitched', '360', 'vr', 'panorama'];
        foreach ($allTags as $key => $val) {
            $keyLower = strtolower((string)$key);
            $valLower = strtolower((string)$val);
            foreach ($keywords as $kw) {
                if (stripos($keyLower, $kw) !== false || stripos($valLower, $kw) !== false) {
                    return true;
                }
            }
        }

        // Strategy 3: Heuristic — relaxed 2:1 aspect ratio (allow lower resolutions)
        foreach ($data['streams'] ?? [] as $stream) {
            if (($stream['codec_type'] ?? '') !== 'video') {
                continue;
            }
            $width  = (int)($stream['width']  ?? 0);
            $height = (int)($stream['height'] ?? 0);
            if ($height > 0) {
                $ratio = $width / $height;
                // 1.9–2.1 range to allow slight variations + minimum sensible size
                if ($ratio >= 1.9 && $ratio <= 2.1 && $width >= 1440) {  // lowered threshold
                    return true;
                }
            }
        }

        return false;
    }
}
