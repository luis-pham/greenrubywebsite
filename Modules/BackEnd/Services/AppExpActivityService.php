<?php
namespace Modules\BackEnd\Services;

use Illuminate\Support\Facades\DB;
use Modules\BackEnd\Entities\AppExpActivity;
use Modules\BackEnd\Entities\AppExpActivitySuitableAudience;
use Modules\BackEnd\Entities\AppFile;
use Modules\BackEnd\Entities\AppFileAttach;
use Modules\BackEnd\Services\AppFileService;


class AppExpActivityService
{
    public static function find($id, $languageId = null)
    {
        $query = AppExpActivity::where('id', $id);
        if ($languageId !== null) {
            $query->where('language_id', $languageId);
        }
        return $query->first();
    }

    public static function create($data, $languageId)
    {
        DB::beginTransaction();
        try {
            $obj = new AppExpActivity();
            $obj->language_id = $languageId;
            $obj->group_id = array_key_exists('group_id', $data) ? $data['group_id'] : null;
            $obj->cruise_id = $data['cruise_id'] ?? null;
            $obj->name = array_key_exists('name', $data) ? $data['name'] : null;
            $obj->summary = array_key_exists('summary', $data) ? $data['summary'] : null;
            $obj->content = array_key_exists('content', $data) ? \App\Support\HtmlSanitizer::clean($data['content']) : null;
            $obj->image_link = array_key_exists('image_link', $data) ? $data['image_link'] : null;
            $obj->cover_link = array_key_exists('cover_link', $data) ? $data['cover_link'] : null;
            $obj->duration = array_key_exists('duration', $data) ? $data['duration'] : null;
            $obj->start_time = array_key_exists('start_time', $data) ? $data['start_time'] : null;
            $obj->end_time = array_key_exists('end_time', $data) ? $data['end_time'] : null;
            $obj->note = array_key_exists('note', $data) ? $data['note'] : null;
            $obj->is_featured = array_key_exists('is_featured', $data) ? $data['is_featured'] : 0;
            $obj->save();

            // Lưu vào bảng trung gian
            $ord = 1;

            // 1. Lưu group_id (type = 3 - LOẠI HOẠT ĐỘNG)
            if (array_key_exists('group_id', $data) && $data['group_id']) {
                $pivot = new AppExpActivitySuitableAudience();
                $pivot->exp_activity_id = $obj->id;
                $pivot->group_id = $data['group_id'];
                $pivot->ord = $ord++;
                $pivot->save();
            }

            // 2. Lưu audience_group_ids từ modal
            if (array_key_exists('audience_group_ids', $data) && is_array($data['audience_group_ids'])) {
                foreach ($data['audience_group_ids'] as $groupId) {
                    if ($groupId) {
                        $pivot = new AppExpActivitySuitableAudience();
                        $pivot->exp_activity_id = $obj->id;
                        $pivot->group_id = $groupId;
                        $pivot->ord = $ord++;
                        $pivot->save();
                    }
                }
            }
            self::syncActivityGallery($obj->id, array_key_exists('activity_gallery', $data) ? $data['activity_gallery'] : '[]');

            DB::commit();

            return $obj->id;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function update($data, $languageId)
    {
        DB::beginTransaction();
        try {
            $obj = self::find($data['id'], $languageId);
            if ($obj) {
                $obj->group_id = array_key_exists('group_id', $data) ? $data['group_id'] : $obj->group_id;
                $obj->cruise_id = $data['cruise_id'] ?? null;
                $obj->name = array_key_exists('name', $data) ? $data['name'] : $obj->name;
                $obj->summary = array_key_exists('summary', $data) ? $data['summary'] : $obj->summary;
                $obj->content = array_key_exists('content', $data) ? \App\Support\HtmlSanitizer::clean($data['content']) : $obj->content;
                $obj->image_link = array_key_exists('image_link', $data) ? $data['image_link'] : $obj->image_link;
                $obj->cover_link = array_key_exists('cover_link', $data) ? $data['cover_link'] : $obj->cover_link;
                $obj->duration = array_key_exists('duration', $data) ? $data['duration'] : $obj->duration;
                $obj->start_time = array_key_exists('start_time', $data) ? $data['start_time'] : $obj->start_time;
                $obj->end_time = array_key_exists('end_time', $data) ? $data['end_time'] : $obj->end_time;
                $obj->note = array_key_exists('note', $data) ? $data['note'] : $obj->note;
                $obj->is_featured = array_key_exists('is_featured', $data) ? $data['is_featured'] : $obj->is_featured;
                $obj->save();

                // Xóa hết quan hệ cũ
                AppExpActivitySuitableAudience::where('exp_activity_id', $obj->id)->delete();

                // Lưu lại vào bảng trung gian
                $ord = 1;

                // 1. Lưu group_id (type = 3 - LOẠI HOẠT ĐỘNG)
                if (array_key_exists('group_id', $data) && $data['group_id']) {
                    $pivot = new AppExpActivitySuitableAudience();
                    $pivot->exp_activity_id = $obj->id;
                    $pivot->group_id = $data['group_id'];
                    $pivot->ord = $ord++;
                    $pivot->save();
                }

                // 2. Lưu audience_group_ids từ modal
                if (array_key_exists('audience_group_ids', $data) && is_array($data['audience_group_ids'])) {
                    foreach ($data['audience_group_ids'] as $groupId) {
                        if ($groupId) {
                            $pivot = new AppExpActivitySuitableAudience();
                            $pivot->exp_activity_id = $obj->id;
                            $pivot->group_id = $groupId;
                            $pivot->ord = $ord++;
                            $pivot->save();
                        }
                    }
                }

                self::syncActivityGallery($obj->id, array_key_exists('activity_gallery', $data) ? $data['activity_gallery'] : '[]');
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function delete($id, $languageId)
    {
        if (is_array($id)) {
            AppExpActivitySuitableAudience::whereIn('exp_activity_id', $id)->delete();
            AppFileAttach::whereIn('object_id', $id)->where('object_type', config('backend.fileAttachObjectType.expActivity'))->delete();

            AppExpActivity::whereIn('id', $id)
                ->where('language_id', $languageId)
                ->delete();
        } else {
            $obj = self::find($id, $languageId);
            if ($obj) {
                AppExpActivitySuitableAudience::where('exp_activity_id', $obj->id)->delete();
                AppFileAttach::where('object_id', $obj->id)->where('object_type', config('backend.fileAttachObjectType.expActivity'))->delete();
                $obj->delete();
            }
        }
    }

    public static function getAll($languageId)
    {
        return AppExpActivity::where('language_id', $languageId)
//            ->orderBy('ord','desc')
            ->get();
    }

    public static function findJoin($id, $languageId = null)
    {
        $query = AppExpActivity::select('app_exp_activity.*', DB::raw('app_group.name AS group_name'))
            ->leftJoin('app_group', function($join) use ($languageId) {
                $join->on('app_group.id', '=', 'app_exp_activity.group_id');
                $join->where('app_group.slug', '!=', 'root');
                if ($languageId !== null) {
                    $join->where('app_group.language_id', $languageId);
                }
            })
            ->where('app_exp_activity.id', $id);
        if ($languageId !== null) {
            $query->where('app_exp_activity.language_id', $languageId);
        }
        $obj = $query->first();

        return $obj;
    }


    public static function getSuitableAudiences($expActivityId, $languageId)
    {
        return AppExpActivitySuitableAudience::select('app_group.id', 'app_group.name')
            ->join('app_group', 'app_group.id', '=', 'app_exp_activity_suitable_audience.group_id')
            ->where('app_exp_activity_suitable_audience.exp_activity_id', $expActivityId)
            ->where('app_group.language_id', $languageId)
            ->where('app_group.type', config('backend.groupType.suitableAudience'))
            ->orderBy('app_exp_activity_suitable_audience.ord')
            ->get()
            ->pluck('name', 'id')
            ->toArray();
    }

    public static function syncActivityGallery($activityId, $activityGalleryJson)
    {
        $objectType = config('backend.fileAttachObjectType.expActivity');
        AppFileAttach::where('object_type', $objectType)
            ->where('object_id', $activityId)
            ->delete();

        $items = json_decode($activityGalleryJson, true);
        if (!is_array($items)) {
            return;
        }
        $ord = 0;
        foreach ($items as $item) {
            $link = isset($item['link']) ? $item['link'] : null;
            if (!$link) {
                continue;
            }
            $fileId = null;
            $existingFile = null;
            $item = is_array($item) ? $item : (array) $item;
            if (!empty($item['id'])) {
                $existingFile = AppFile::find($item['id']);
                if ($existingFile) {
                    $fileId = (int) $item['id'];
                }
            }
            if (!$fileId) {
                $file = AppFile::where('link', $link)->first();
                if ($file) {
                    $fileId = $file->id;
                } else {
                    $fileData = [
                        'name' => isset($item['title']) ? $item['title'] : $item['name'] ?? basename($link),
                        'link' => $link,
                        'thumbnail' => $item['thumbnail'] ?? null,
                        'description' => $item['description'] ?? null,
                    ];
                    if (isset($item['extension'])) {
                        $fileData['extension'] = $item['extension'];
                    }
                    if (isset($item['size'])) {
                        $fileData['size'] = $item['size'];
                    }
                    $fileId = AppFileService::create($fileData);
                }
            }
            if ($fileId && !empty($existingFile)) {
                $updateData = ['id' => $fileId];
                if (isset($item['title'])) {
                    $updateData['name'] = $item['title'];
                }
                if (array_key_exists('description', $item)) {
                    $updateData['description'] = $item['description'];
                }
                if (count($updateData) > 1) {
                    AppFileService::update($updateData);
                }
            }
            $ord++;
            $attach = new AppFileAttach();
            $attach->file_id = $fileId;
            $attach->object_id = $activityId;
            $attach->object_type = $objectType;
            $attach->ord = $ord;
            $attach->save();
        }
    }

    public static function getActivityGallery($activityId)
    {
        $objectType = config('backend.fileAttachObjectType.expActivity');
        $rows = DB::table('app_file_attach')
            ->join('app_file', 'app_file.id', '=', 'app_file_attach.file_id')
            ->where('app_file_attach.object_type', $objectType)
            ->where('app_file_attach.object_id', $activityId)
            ->orderBy('app_file_attach.ord')
            ->select(
                'app_file.id',
                'app_file.name',
                'app_file.link',
                'app_file.thumbnail',
                'app_file.description'
            )
            ->get();
        $list = [];
        foreach ($rows as $row) {
            $list[] = (object) [
                'id' => $row->id,
                'title' => $row->name,
                'name' => $row->name,
                'link' => $row->link,
                'thumbnail' => $row->thumbnail,
                'description' => $row->description ?? '',
            ];
        }
        return $list;
    }
    public static function getPaging($param, $languageId = null)
    {
        $list = new AppExpActivity();
        $list = $list->select('app_exp_activity.*', DB::raw('app_group.name AS group_name'));
        $list = $list->leftJoin('app_group', function($join) use ($languageId) {
            $join->on('app_group.id', '=', 'app_exp_activity.group_id');
            $join->where('app_group.slug', '!=', 'root');
            if ($languageId) {
                $join->where('app_group.language_id', $languageId);
            }
        });

        if (array_key_exists('keyword', $param)) {
            $list = $list->where(function($query) use ($param) {
                $query->where('app_exp_activity.name', 'like', '%' . $param['keyword'] . '%')
                      ->orWhere('summary', 'like', '%' . $param['keyword'] . '%')
                      ->orWhere('content', 'like', '%' . $param['keyword'] . '%');
            });
        }

        if (array_key_exists('group_id', $param)) {
            $list = $list->where('app_exp_activity.group_id', $param['group_id']);
        }


        if ($languageId) {
            $list = $list->where('app_exp_activity.language_id', $languageId);
        }
        $list = $list->orderBy('ord');
        return $list->paginate(config('backend.paginationLimit'));
    }

    public static function getById($id, $languageId)
    {
        if (is_array($id)) {
            return AppExpActivity::whereIn('id', $id)
                ->where('language_id', $languageId)
                ->get();
        } else {
            return self::find($id, $languageId);
        }
    }

    public static function getExpActivityFeatured($languageId)
    {
        return AppExpActivity::where('language_id', $languageId)
            ->where('is_featured', 1)
            ->orderBy('id', 'desc')
            ->get();
    }

    public static function getLatestUpdate($param, $languageId = null)
    {
        $query = AppExpActivity::select('app_exp_activity.*', DB::raw('IFNULL(app_exp_activity.updated_at, app_exp_activity.created_at) AS `lastmod`'));

        if ($languageId) {
            $query = $query->where('app_exp_activity.language_id', $languageId);
        }
        $query = $query->orderBy('updated_at', 'desc');
        return $query->first();
    }
}
