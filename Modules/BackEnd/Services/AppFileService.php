<?php
namespace Modules\BackEnd\Services;

use Illuminate\Support\Facades\DB;
use Modules\BackEnd\Helpers\Utilities;
use Modules\BackEnd\Entities\AppFile;
use Modules\BackEnd\Entities\AppFileAttach;

class AppFileService
{
    public static function find($id)
    {
        return AppFile::find($id);
    }

    public static function create($data)
    {
        $obj = new AppFile();
        $obj->name = array_key_exists('name', $data) ? $data['name'] : null;
        $obj->link = array_key_exists('link', $data) ? $data['link'] : null;
        $obj->thumbnail = array_key_exists('thumbnail', $data) ? $data['thumbnail'] : null;
        $obj->size = array_key_exists('size', $data) ? $data['size'] : null;
        $obj->extension = array_key_exists('extension', $data) ? $data['extension'] : null;
        $obj->description = array_key_exists('description', $data) ? $data['description'] : null;
        $obj->is_360 = array_key_exists('is_360', $data) ? $data['is_360'] : null;
        $obj->save();

        return $obj->id;
    }

    public static function update($data)
    {
        $obj = self::find($data['id']);
        if ($obj) {
            $obj->name = array_key_exists('name', $data) ? $data['name'] : $obj->name;
            $obj->link = array_key_exists('link', $data) ? $data['link'] : $obj->link;
            $obj->thumbnail = array_key_exists('thumbnail', $data) ? $data['thumbnail'] : $obj->thumbnail;
            $obj->size = array_key_exists('size', $data) ? $data['size'] : $obj->size;
            $obj->extension = array_key_exists('extension', $data) ? $data['extension'] : $obj->extension;
            $obj->description = array_key_exists('description', $data) ? $data['description'] : $obj->description;
            $obj->save();
        }
    }

    public static function delete($id)
    {
        DB::beginTransaction();
        try {
            if (is_array($id)) {
                $list = AppFile::whereIn('id', $id)->get();
                for ($i = 0; $i < count($list); $i++) {
                    $link = Utilities::getFileLink($list[$i]->link);
                    @unlink(ltrim($link, '/'));

                    if ($list[$i]->thumbnail) {
                        $thumbnail = Utilities::getFileLink($list[$i]->thumbnail);
                        @unlink(ltrim($thumbnail, '/'));
                    }
                }

                AppFileAttach::whereIn('file_id', $id)->delete();
                AppFile::destroy($id);
            } else {
                $obj = self::find($id);
                if ($obj) {
                    $link = Utilities::getFileLink($obj->link);
                    @unlink(ltrim($link, '/'));
                    if ($obj->thumbnail) {
                        $thumbnail = Utilities::getFileLink($obj->thumbnail);
                        @unlink(ltrim($thumbnail, '/'));
                    }
                    AppFileAttach::where('file_id', $id)->delete();
                    $obj->delete();
                }
            }

            DB::commit();

            return $obj->id;
        } catch (\Exception $e) {
            DB::rollBack();

            throw $e;
        }
    }

    public static function getAll()
    {
        return AppFile::all();
    }

    public static function getPaging($param)
    {
        $list = new AppFile();
        if (array_key_exists('keyword', $param)) {
            $list = $list->where('name', 'like', '%' . $param['keyword'] . '%');
        }
        if (array_key_exists('extension', $param)) {
            $list = $list->whereIn('extension', $param['extension']);
        }
        if (array_key_exists('exclude_id', $param)) {
            if (is_array($param['exclude_id'])) {
                $list = $list->whereNotIn('id', $param['exclude_id']);
            } else {
                $list = $list->where('id', '!=', $param['exclude_id']);
            }
        }
        if (array_key_exists('from_date', $param) && $param['from_date']) {
            $list = $list->whereDate('created_at', '>=', $param['from_date']);
        }
        if (array_key_exists('to_date', $param) && $param['to_date']) {
            $list = $list->whereDate('created_at', '<', $param['to_date']);
        }
        $list = $list->orderBy('id', 'desc');
        return $list->paginate(30);
    }
}
