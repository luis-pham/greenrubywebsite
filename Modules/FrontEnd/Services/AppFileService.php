<?php
namespace Modules\FrontEnd\Services;

use Modules\BackEnd\Entities\AppFile;

class AppFileService
{
    public static function getByObjectId($objectId, $objectType)
    {
        $query = new AppFile();
        $query = $query->select('app_file.*', \DB::raw('app_file_attach.object_id'));
        $query = $query->join('app_file_attach', function($join) use ($objectId, $objectType) {
            $join->on('app_file.id', '=', 'app_file_attach.file_id');
            if (is_array($objectId)) {
                $join->whereIn('app_file_attach.object_id', $objectId);
            } else {
                $join->where('app_file_attach.object_id', $objectId);
            }
            $join->where('app_file_attach.object_type', $objectType);
        });
        $query = $query->orderBy('app_file_attach.object_id');
        $query = $query->orderBy('app_file_attach.ord');
        return $query->get();
    }
}