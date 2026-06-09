<?php

namespace Modules\FrontEnd\Services;

use Illuminate\Support\Facades\DB;
use Modules\BackEnd\Entities\AppFile;
use Modules\BackEnd\Entities\AppService;

class AppServiceService
{
    public static function getFileById($id){
        $list = new AppFile();
        $list = $list->select(DB::raw('app_file.*, app_file_attach.object_id, app_file_attach.ord'));
        $list = $list->join('app_file_attach', 'app_file.id', '=', 'app_file_attach.file_id');
        $list = $list->where('object_type', config('backend.fileAttachObjectType.service'));
        if (is_array($id)) {
            $list = $list->whereIn('object_id', $id);
        } else {
            $list = $list->where('object_id', $id);
        }
        $list = $list->orderBy('object_id');
        $list = $list->orderBy('ord');
        return $list->get();
    }

    public static function getAllByType($type,$languageId){
        return AppService::where('language_id',$languageId)->where('type',$type)->get();
    }
}
