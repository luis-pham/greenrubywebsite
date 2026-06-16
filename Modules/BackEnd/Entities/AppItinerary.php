<?php
namespace Modules\BackEnd\Entities;

class AppItinerary extends BaseModel
{
    protected $table = 'app_itinerary';
    protected $fillable = [
        'language_id',
        'name',
        'description',
        'seo_title',
        'seo_description',
        'duration',
        'image_link',
        'important_note',
        'destination',
        'start_time',
        'bay',
        'cover_link'
    ];
    protected $hidden = [];

    protected $casts = [
        'important_note' => 'collection',
    ];

    public function itineraryServices(){
        return $this
            ->belongsToMany(AppService::class,'app_itinerary_service','itineraries_id','service_id')
            ->withPivot('ord')
            ->orderByPivot('ord');
    }

    public function itineraryActivities(){
        return $this
            ->belongsToMany(AppExpActivity::class,'app_itinerary_exp_activity','itineraries_id','exp_activity_id')
            ->withPivot('ord')
            ->orderByPivot('ord');
    }

    public function itineraryDays(){
        return $this->hasMany(AppItineraryDay::class,'itinerary_id','id');
    }

    public function cruises(){
        return $this->belongsToMany(AppCruise::class,'app_cruise_itinerary','itinerary_id','cruise_id')
            ->withPivot('start_at')
            ->where('start_at','>=',date('Y-m-d'))
            ->orderByPivot('start_at');
    }

    public function galleryImages()
    {
        return $this->belongsToMany(
            AppFile::class,
            'app_file_attach',       // pivot table
            'object_id',             // foreign key for this model (itinerary id)
            'file_id',               // foreign key for AppFile
            'id',                    // local key
            'id'                     // related key
        )
        ->where('object_type', config('backend.fileAttachObjectType.itinerary'))  // ← 5
        ->withPivot('ord')
        ->orderByPivot('ord');
    }

    public function syncGalleryImages($idsWithPivot){
        $prepared = collect($idsWithPivot)->mapWithKeys(function ($pivot, $id) {
            return [$id => array_merge($pivot, ['object_type' => config('backend.fileAttachObjectType.itinerary')])];
        })->all();

        return $this->galleryImages()->sync($prepared);
    }

    public static function boot(){
        parent::boot();

        static::deleted(function($itinerary){
            $itinerary->itineraryServices()->detach();
            $itinerary->itineraryActivities()->detach();
            $itinerary->galleryImages()->detach();
            foreach ($itinerary->itineraryDays as $day) {
                $day->itineraryDayDetails()->delete();
                $day->delete();
            }
        });
    }
}
