<?php
namespace Modules\BackEnd\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\BackEnd\Entities\AppCabin;
use Modules\BackEnd\Entities\AppCabinPrice;
use Modules\BackEnd\Entities\AppCabinRoom;
use Modules\BackEnd\Entities\AppCabinAmenity;
use Modules\BackEnd\Entities\AppCabinSuitableAudience;
use Modules\BackEnd\Entities\AppFile;
use Modules\BackEnd\Entities\AppFileAttach;
use Modules\BackEnd\Entities\AppAmenity;
use Modules\BackEnd\Entities\AppGroup;
use Modules\BackEnd\Services\AppFileService;
use Modules\BackEnd\Services\AppGroupService;

class AppCabinService
{
    public static function find($id, $languageId = null)
    {
        $query = AppCabin::where('id', $id);
        if ($languageId !== null) {
            $query->where('language_id', $languageId);
        }
        return $query->first();
    }

    public static function create($data, $languageId)
    {
        DB::beginTransaction();
        try {
            $obj = new AppCabin();
            $obj->language_id = $languageId;
            $obj->group_id = array_key_exists('group_id', $data) ? $data['group_id'] : null;
            $obj->cruise_id = array_key_exists('cruise_id', $data) ? $data['cruise_id'] : null;
            $obj->name = array_key_exists('name', $data) ? $data['name'] : null;
            $obj->summary = array_key_exists('summary', $data) ? $data['summary'] : null;
            $obj->content = array_key_exists('content', $data) ? $data['content'] : null;
            $obj->image_link = array_key_exists('image_link', $data) && !empty($data['image_link']) ? $data['image_link'] : null;
            $obj->view = array_key_exists('view', $data) ? $data['view'] : null;
            $obj->cabin_class = array_key_exists('cabin_class', $data) ? $data['cabin_class'] : null;
            $obj->capacity = array_key_exists('capacity', $data) ? $data['capacity'] : null;
            $obj->over_capacity_adult = array_key_exists('over_capacity_adult', $data) ? $data['over_capacity_adult'] : 0;
            $obj->over_capacity_child_6_12 = array_key_exists('over_capacity_child_6_12', $data) ? $data['over_capacity_child_6_12'] : 0;
            $obj->over_capacity_child_2_5 = array_key_exists('over_capacity_child_2_5', $data) ? $data['over_capacity_child_2_5'] : 0;
            $obj->over_capacity_infant = array_key_exists('over_capacity_infant', $data) ? $data['over_capacity_infant'] : 0;
            $obj->area = array_key_exists('area', $data) ? $data['area'] : null;
            $obj->discount_percent = array_key_exists('discount_percent', $data) ? $data['discount_percent'] : null;
            $obj->ord = array_key_exists('ord', $data) ? $data['ord'] : 0;
            $obj->save();

            if (array_key_exists('room_title', $data)) {
                for ($i = 0; $i < count($data['room_title']); $i++) {
                    if (!trim($data['room_title'][$i])) {
                        continue;
                    }
                    $room = new AppCabinRoom();
                    $room->cabin_id = $obj->id;
                    $room->title = $data['room_title'][$i];
                    $room->description = array_key_exists('room_description', $data) && array_key_exists($i, $data['room_description']) ? $data['room_description'][$i] : null;
                    $room->ord = $i + 1;
                    $room->save();
                }
            }

            if (array_key_exists('amenity_ids', $data)) {
                for ($i = 0; $i < count($data['amenity_ids']); $i++) {
                    if (!$data['amenity_ids'][$i]) {
                        continue;
                    }
                    $pivot = new AppCabinAmenity();
                    $pivot->cabin_id = $obj->id;
                    $pivot->amenity_id = $data['amenity_ids'][$i];
                    $pivot->ord = $i + 1;
                    $pivot->save();
                }
            }

            if (array_key_exists('amenity_name', $data)) {
                for ($i = 0; $i < count($data['amenity_name']); $i++) {
                    if (!trim($data['amenity_name'][$i])) {
                        continue;
                    }
                    $amenity = new AppAmenity();
                    $amenity->language_id = $languageId;
                    $amenity->name = $data['amenity_name'][$i];
                    $amenity->description = array_key_exists('amenity_description', $data) && array_key_exists($i, $data['amenity_description']) ? $data['amenity_description'][$i] : null;
                    $amenity->icon = array_key_exists('amenity_icon', $data) && array_key_exists($i, $data['amenity_icon']) ? $data['amenity_icon'][$i] : null;
                    $amenity->ord = 0;
                    $amenity->save();

                    $pivot = new AppCabinAmenity();
                    $pivot->cabin_id = $obj->id;
                    $pivot->amenity_id = $amenity->id;
                    $pivot->ord = 0;
                    $pivot->save();
                }
            }

            if (array_key_exists('price', $data) && is_array($data['price'])) {
                AppCabinPrice::where('cabin_id', $obj->id)->delete();
                foreach ($data['price'] as $duration => $guests) {
                    if (is_array($guests)) {
                        foreach ($guests as $guest => $price) {
                            if ($price !== null && $price !== '') {
                                $priceObj = new AppCabinPrice();
                                $priceObj->cabin_id = $obj->id;
                                $priceObj->duration = $duration;
                                $priceObj->guest = $guest;
                                $priceObj->price = $price;
                                $priceObj->save();
                            }
                        }
                    }
                }
            }

            if (array_key_exists('audience_group_ids', $data) && is_array($data['audience_group_ids'])) {
                for ($i = 0; $i < count($data['audience_group_ids']); $i++) {
                    $groupId = $data['audience_group_ids'][$i];
                    if (!$groupId) {
                        continue;
                    }
                    $pivot = new AppCabinSuitableAudience();
                    $pivot->cabin_id = $obj->id;
                    $pivot->group_id = $groupId;
                    $pivot->ord = $i + 1;
                    $pivot->save();
                }
            }

            self::syncCabinGallery($obj->id, array_key_exists('image_gallery', $data) ? $data['image_gallery'] : '[]');

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
            if (!$obj) {
                DB::rollBack();
                throw new \InvalidArgumentException('Cabin not found.');
            }
            $obj->group_id = array_key_exists('group_id', $data) ? $data['group_id'] : $obj->group_id;
                $obj->cruise_id = array_key_exists('cruise_id', $data) ? $data['cruise_id'] : $obj->cruise_id;
                $obj->name = array_key_exists('name', $data) ? $data['name'] : $obj->name;
                $obj->summary = array_key_exists('summary', $data) ? $data['summary'] : $obj->summary;
                $obj->content = array_key_exists('content', $data) ? $data['content'] : $obj->content;
                $obj->image_link = array_key_exists('image_link', $data) && !empty($data['image_link']) ? $data['image_link'] : (array_key_exists('image_link', $data) && $data['image_link'] === '' ? null : $obj->image_link);
                $obj->view = array_key_exists('view', $data) ? $data['view'] : $obj->view;
                $obj->cabin_class = array_key_exists('cabin_class', $data) ? $data['cabin_class'] : $obj->cabin_class;
                $obj->capacity = array_key_exists('capacity', $data) ? $data['capacity'] : $obj->capacity;
                $obj->over_capacity_adult = array_key_exists('over_capacity_adult', $data) ? $data['over_capacity_adult'] : $obj->over_capacity_adult;
                $obj->over_capacity_child_6_12 = array_key_exists('over_capacity_child_6_12', $data) ? $data['over_capacity_child_6_12'] : $obj->over_capacity_child_6_12;
                $obj->over_capacity_child_2_5 = array_key_exists('over_capacity_child_2_5', $data) ? $data['over_capacity_child_2_5'] : $obj->over_capacity_child_2_5;
                $obj->over_capacity_infant = array_key_exists('over_capacity_infant', $data) ? $data['over_capacity_infant'] : $obj->over_capacity_infant;
                $obj->area = array_key_exists('area', $data) ? $data['area'] : $obj->area;
                $obj->discount_percent = array_key_exists('discount_percent', $data) ? $data['discount_percent'] : $obj->discount_percent;
                $obj->ord = array_key_exists('ord', $data) ? $data['ord'] : $obj->ord;
                $obj->save();

                AppCabinRoom::where('cabin_id', $obj->id)->delete();
                if (array_key_exists('room_title', $data)) {
                    for ($i = 0; $i < count($data['room_title']); $i++) {
                        if (!trim($data['room_title'][$i])) {
                            continue;
                        }
                        $room = new AppCabinRoom();
                        $room->cabin_id = $obj->id;
                        $room->title = $data['room_title'][$i];
                        $room->description = array_key_exists('room_description', $data) && array_key_exists($i, $data['room_description']) ? $data['room_description'][$i] : null;
                        $room->ord = $i + 1;
                        $room->save();
                    }
                }

                AppCabinAmenity::where('cabin_id', $obj->id)->delete();
                if (array_key_exists('amenity_ids', $data)) {
                    for ($i = 0; $i < count($data['amenity_ids']); $i++) {
                        if (!$data['amenity_ids'][$i]) {
                            continue;
                        }
                        $pivot = new AppCabinAmenity();
                        $pivot->cabin_id = $obj->id;
                        $pivot->amenity_id = $data['amenity_ids'][$i];
                        $pivot->ord = $i + 1;
                        $pivot->save();
                    }
                }

                if (array_key_exists('amenity_name', $data)) {
                    for ($i = 0; $i < count($data['amenity_name']); $i++) {
                        if (!trim($data['amenity_name'][$i])) {
                            continue;
                        }
                        $amenity = new AppAmenity();
                        $amenity->language_id = $languageId;
                        $amenity->name = $data['amenity_name'][$i];
                        $amenity->description = array_key_exists('amenity_description', $data) && array_key_exists($i, $data['amenity_description']) ? $data['amenity_description'][$i] : null;
                        $amenity->icon = array_key_exists('amenity_icon', $data) && array_key_exists($i, $data['amenity_icon']) ? $data['amenity_icon'][$i] : null;
                        $amenity->ord = 0;
                        $amenity->save();

                        $pivot = new AppCabinAmenity();
                        $pivot->cabin_id = $obj->id;
                        $pivot->amenity_id = $amenity->id;
                        $pivot->ord = 0;
                        $pivot->save();
                    }
                }

                if (array_key_exists('price', $data) && is_array($data['price'])) {
                    AppCabinPrice::where('cabin_id', $obj->id)->delete();
                    foreach ($data['price'] as $duration => $guests) {
                        if (is_array($guests)) {
                            foreach ($guests as $guest => $price) {
                                if ($price !== null && $price !== '') {
                                    $priceObj = new AppCabinPrice();
                                    $priceObj->cabin_id = $obj->id;
                                    $priceObj->duration = $duration;
                                    $priceObj->guest = $guest;
                                    $priceObj->price = $price;
                                    $priceObj->save();
                                }
                            }
                        }
                    }
                }

                AppCabinSuitableAudience::where('cabin_id', $obj->id)->delete();
                if (array_key_exists('audience_group_ids', $data) && is_array($data['audience_group_ids'])) {
                    for ($i = 0; $i < count($data['audience_group_ids']); $i++) {
                        $groupId = $data['audience_group_ids'][$i];
                        if (!$groupId) {
                            continue;
                        }
                        $pivot = new AppCabinSuitableAudience();
                        $pivot->cabin_id = $obj->id;
                        $pivot->group_id = $groupId;
                        $pivot->ord = $i + 1;
                        $pivot->save();
                    }
                }

                AppFileAttach::where('object_type', config('backend.fileAttachObjectType.cabin'))
                    ->where('object_id', $obj->id)
                    ->delete();
                self::syncCabinGallery($obj->id, array_key_exists('image_gallery', $data) ? $data['image_gallery'] : '[]');

            DB::commit();
            return $obj->id;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function syncCabinGallery($cabinId, $imageGalleryJson)
    {
        $objectType = config('backend.fileAttachObjectType.cabin');
        AppFileAttach::where('object_type', $objectType)
            ->where('object_id', $cabinId)
            ->delete();

        $items = json_decode($imageGalleryJson, true);
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
            $attach->object_id = $cabinId;
            $attach->object_type = $objectType;
            $attach->ord = $ord;
            $attach->save();
        }
    }

    public static function getCabinGallery($cabinId)
    {
        $objectType = config('backend.fileAttachObjectType.cabin');
        $rows = DB::table('app_file_attach')
            ->join('app_file', 'app_file.id', '=', 'app_file_attach.file_id')
            ->where('app_file_attach.object_type', $objectType)
            ->where('app_file_attach.object_id', $cabinId)
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

    public static function delete($id, $languageId = null)
    {
        $ids = is_array($id) ? $id : [$id];
        if (empty($ids)) {
            return;
        }
        $query = AppCabin::whereIn('id', $ids);
        if ($languageId !== null) {
            $query->where('language_id', $languageId);
        }
        $ids = $query->pluck('id')->toArray();
        if (empty($ids)) {
            return;
        }
        DB::beginTransaction();
        try {
            AppCabinPrice::whereIn('cabin_id', $ids)->delete();
            AppCabinRoom::whereIn('cabin_id', $ids)->delete();
            AppCabinAmenity::whereIn('cabin_id', $ids)->delete();
            AppCabinSuitableAudience::whereIn('cabin_id', $ids)->delete();
            AppFileAttach::where('object_type', config('backend.fileAttachObjectType.cabin'))
                ->whereIn('object_id', $ids)
                ->delete();
            AppCabin::whereIn('id', $ids)->delete();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function getAll($languageId = null)
    {
        $query = new AppCabin();
        if ($languageId !== null) {
            $query = $query->where('language_id', $languageId);
        }
        return $query->get();
    }

    public static function findJoin($id, $languageId = null)
    {
        $query = AppCabin::select('app_cabin.*', DB::raw('app_cruise.name AS cruise_name'))
            ->leftJoin('app_cruise', 'app_cruise.id', '=', 'app_cabin.cruise_id')
            ->where('app_cabin.id', $id);
        
        if ($languageId !== null) {
            $query->where('app_cabin.language_id', $languageId);
        }
        
        return $query->first();
    }

    public static function getPaging($param, $languageId = null)
    {
        $list = AppCabin::query()
            ->from('app_cabin')
            ->select('app_cabin.*', 
                DB::raw('app_cruise.name AS cruise_name'),
                DB::raw('app_group.name AS group_name'),
                DB::raw('(SELECT MIN(price) FROM app_cabin_price WHERE app_cabin_price.cabin_id = app_cabin.id) AS min_price'))
            ->leftJoin('app_cruise', 'app_cruise.id', '=', 'app_cabin.cruise_id')
            ->leftJoin('app_group', function($join) use ($languageId) {
                $join->on('app_group.id', '=', 'app_cabin.group_id')
                     ->where('app_group.type', '=', config('backend.groupType.cabin'));
                if ($languageId !== null) {
                    $join->where('app_group.language_id', '=', $languageId);
                }
            });

        if (array_key_exists('keyword', $param)) {
            $list = $list->where(function ($query) use ($param) {
                $query->where('app_cabin.name', 'like', '%' . $param['keyword'] . '%');
                $query->orWhere('app_cabin.summary', 'like', '%' . $param['keyword'] . '%');
                $query->orWhere('app_cabin.content', 'like', '%' . $param['keyword'] . '%');
            });
        }
        if (array_key_exists('cruise_id', $param)) {
            $list = $list->where('app_cabin.cruise_id', $param['cruise_id']);
        }
        if (array_key_exists('group_id', $param)) {
            $list = $list->where('app_cabin.group_id', $param['group_id']);
        }
        if ($languageId !== null) {
            $list = $list->where('app_cabin.language_id', $languageId);
        }
        $list = $list->orderBy('app_cabin.ord', 'asc');
        return $list->paginate(config('backend.paginationLimit'));
    }

    public static function getById($id, $languageId = null)
    {
        if (is_array($id)) {
            $query = AppCabin::whereIn('id', $id);
            if ($languageId !== null) {
                $query->where('language_id', $languageId);
            }
            return $query->get();
        } else {
            return self::find($id, $languageId);
        }
    }
}
