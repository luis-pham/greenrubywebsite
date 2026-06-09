<?php

namespace Modules\FrontEnd\Services;

use Modules\BackEnd\Entities\AppFileAttach;

class AppFileAttachService
{
    static function findByFileId($fileId){
        return AppFileAttach::where('file_id',$fileId)->get();
    }
}
