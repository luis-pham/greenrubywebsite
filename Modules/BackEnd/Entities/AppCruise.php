<?php
namespace Modules\BackEnd\Entities;

use Modules\BackEnd\Entities\BaseModel;

class AppCruise extends BaseModel
{
    protected $table = 'app_cruise';
    protected $fillable = [
        'language_id',
        'name',
        'summary',
        'content',
        'image_link',
        'star_rating',
        'capacity',
        'total_floor',
        'dimension_length',
        'year_built',
        'description_design',
        'green_technology',
        'cover_link'
    ];
    protected $hidden = [];

    protected $casts = [
        'green_technology' => 'object'
    ];

    public function cabins()
    {
        return $this->hasMany(AppCabin::class, 'cruise_id', 'id');
    }

    public function cruiseAmenities()
    {
        return $this->belongsToMany(AppAmenity::class,'app_cruise_amenity','cruise_id','amenity_id')->withPivot('ord')->orderByPivot('ord');
    }

    public function cruiseServices()
    {
        return $this->belongsToMany(AppService::class,'app_cruise_service','cruise_id','service_id')->withPivot('ord')->orderByPivot('ord');
    }

    public function itineraries(){
        return $this->belongsToMany(AppItinerary::class,'app_cruise_itinerary','cruise_id','itinerary_id')
            ->withPivot('start_at')
            ->wherePivot('start_at','>=',date('Y-m-d'))
            ->orderByPivot('start_at');
    }

    public function galleryImages(){
        return $this->belongsToMany(
            AppFile::class,
            'app_file_attach',       // pivot table
            'object_id',             // foreign key for this model
            'file_id',               // foreign key for AppFile
            'id',                    // local key
            'id'                     // related key
        )
            ->where('object_type', config('backend.fileAttachObjectType.cruise'))
            ->withPivot('ord')
            ->orderByPivot('ord');
    }

    public function syncGalleryImages($idsWithPivot) {
        $prepared = collect($idsWithPivot)->mapWithKeys(function ($pivot, $id) {
            return [$id => array_merge($pivot, ['object_type' => config('backend.fileAttachObjectType.cruise')])];
        })->all();

        return $this->galleryImages()->sync($prepared);
    }

    protected static function boot()
    {
        parent::boot();

        static::deleted(function ($model) {
            $model->cabins()->delete();
            $model->cruiseAmenities()->detach();
            $model->itineraries()->detach();
            $model->cruiseServices()->detach();
        });
    }
}
