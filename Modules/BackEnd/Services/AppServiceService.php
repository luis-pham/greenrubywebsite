<?php
namespace Modules\BackEnd\Services;

use Illuminate\Support\Facades\DB;
use Modules\BackEnd\Entities\AppService;
use Modules\BackEnd\Entities\AppItineraryService;
use Modules\BackEnd\Entities\AppFile;
use Modules\BackEnd\Entities\AppFileAttach;
use Modules\BackEnd\Services\AppFileService;


class AppServiceService
{
    public static function find($id, $languageId = null)
    {
        $query = AppService::where('id', $id);
        if ($languageId !== null) {
            $query->where('language_id', $languageId);
        }
        return $query->first();
    }

    public static function create($data, $languageId)
    {
        DB::beginTransaction();
        try {
            $obj = new AppService();
            $obj->language_id = $languageId;
            $obj->group_id = array_key_exists('group_id', $data) ? $data['group_id'] : null;
            $obj->name = array_key_exists('name', $data) ? $data['name'] : null;
            $obj->description = array_key_exists('description', $data) ? $data['description'] : null;
            $obj->image_link = array_key_exists('image_link', $data) ? $data['image_link'] : null;
            $obj->price = array_key_exists('price', $data) ? $data['price'] : null;
            $obj->type = array_key_exists('type', $data) ? $data['type'] : null;
            $obj->status = array_key_exists('status', $data) ? $data['status'] : null;
            $obj->save();

            self::syncServiceGallery($obj->id, array_key_exists('service_gallery', $data) ? $data['service_gallery'] : '[]');

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
                $obj->name = array_key_exists('name', $data) ? $data['name'] : $obj->name;
                $obj->description = array_key_exists('description', $data) ? $data['description'] : $obj->description;
                $obj->image_link = array_key_exists('image_link', $data) ? $data['image_link'] : $obj->image_link;
                $obj->price = array_key_exists('price', $data) ? $data['price'] : $obj->price;
                $obj->type = array_key_exists('type', $data) ? $data['type'] : $obj->type;
                $obj->status = array_key_exists('status', $data) ? $data['status'] : $obj->status;
                $obj->save();

                self::syncServiceGallery($obj->id, array_key_exists('service_gallery', $data) ? $data['service_gallery'] : '[]');
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
            AppItineraryService::whereIn('service_id', $id)->delete();
            AppFileAttach::whereIn('object_id', $id)->where('object_type', config('backend.fileAttachObjectType.service'))->delete();

            AppService::whereIn('id', $id)
                ->where('language_id', $languageId)
                ->delete();
        } else {
            $obj = self::find($id, $languageId);
            if ($obj) {
                AppItineraryService::where('service_id', $obj->id)->delete();
                AppFileAttach::where('object_id', $obj->id)->where('object_type', config('backend.fileAttachObjectType.service'))->delete();
                $obj->delete();
            }
        }
    }

    public static function getAll($languageId)
    {
        return AppService::where('language_id', $languageId)
//            ->orderBy('ord','desc')
            ->get();
    }

    public static function getByType($type, $languageId)
    {
        return AppService::where('type', $type)
            ->where('language_id', $languageId)
            ->get();
    }

    public static function findJoin($id, $languageId = null)
    {
        $query = AppService::select('app_service.*', DB::raw('app_group.name AS group_name'))
            ->leftJoin('app_group', function($join) use ($languageId) {
                $join->on('app_group.id', '=', 'app_service.group_id');
                $join->where('app_group.slug', '!=', 'root');
                if ($languageId !== null) {
                    $join->where('app_group.language_id', $languageId);
                }
            })
            ->where('app_service.id', $id);
        if ($languageId !== null) {
            $query->where('app_service.language_id', $languageId);
        }
        return $query->first();
    }

    public static function getPaging($param, $languageId)
    {
        $list = new AppService();
        $list = $list->select('app_service.*', DB::raw('app_group.name AS group_name'));
        $list = $list->leftJoin('app_group', function($join) use ($languageId) {
            $join->on('app_group.id', '=', 'app_service.group_id');
            $join->where('app_group.slug', '!=', 'root');
            $join->where('app_group.language_id', $languageId);
        });

        if (array_key_exists('keyword', $param)) {
            $list = $list->where(function($query) use ($param) {
                $query->where('app_service.name', 'like', '%' . $param['keyword'] . '%')
                      ->orWhere('app_service.description', 'like', '%' . $param['keyword'] . '%');
            });
        }

        $list = $list->where('app_service.language_id', $languageId);
        $list = $list->orderBy('ord');
        return $list->paginate(config('backend.paginationLimit'));

    }

    public static function getById($id, $languageId)
    {
        if (is_array($id)) {
            return AppService::whereIn('id', $id)
                ->where('language_id', $languageId)
                ->get();
        } else {
            return self::find($id, $languageId);
        }
    }

    public static function syncServiceGallery($serviceId, $serviceGalleryJson)
    {
        $objectType = config('backend.fileAttachObjectType.service');
        AppFileAttach::where('object_type', $objectType)
            ->where('object_id', $serviceId)
            ->delete();

        $items = json_decode($serviceGalleryJson, true);
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
            $attach->object_id = $serviceId;
            $attach->object_type = $objectType;
            $attach->ord = $ord;
            $attach->save();
        }
    }

    public static function getServiceGallery($serviceId)
    {
        $objectType = config('backend.fileAttachObjectType.service');
        $rows = DB::table('app_file_attach')
            ->join('app_file', 'app_file.id', '=', 'app_file_attach.file_id')
            ->where('app_file_attach.object_type', $objectType)
            ->where('app_file_attach.object_id', $serviceId)
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

    public static function getLatestUpdate($param, $languageId = null)
    {
        $query = AppService::select('app_service.*', DB::raw('IFNULL(app_service.updated_at, app_service.created_at) AS `lastmod`'));
        if ($languageId) {
            $query = $query->where('app_service.language_id', $languageId);
        }
        $query = $query->orderBy('updated_at', 'desc');
        
        return $query->first();
    }
}