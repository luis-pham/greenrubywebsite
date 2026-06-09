<?php
namespace Modules\BackEnd\Entities;

use Modules\BackEnd\Entities\BaseModel;

class AppCabin extends BaseModel
{
    protected $table = 'app_cabin';
    protected $fillable = [
        'language_id',
        'group_id',
        'cruise_id',
        'name',
        'summary',
        'content',
        'image_link',
        'view',
        'cabin_class',
        'capacity',
        'over_capacity_adult',
        'over_capacity_child_6_12',
        'over_capacity_child_2_5',
        'over_capacity_infant',
        'area',
        'discount_percent',
        'ord'
    ];
    protected $hidden = [];

    public function cruise()
    {
        return $this->belongsTo(AppCruise::class, 'cruise_id', 'id');
    }

    public function cabinPrices()
    {
        return $this->hasMany(AppCabinPrice::class, 'cabin_id', 'id');
    }

    public function cabinRooms()
    {
        return $this->hasMany(AppCabinRoom::class, 'cabin_id', 'id');
    }

    public function cabinAmenities()
    {
        return $this->hasMany(AppCabinAmenity::class, 'cabin_id', 'id');
    }

    public function cabinSuitableAudiences()
    {
        return $this->hasMany(AppCabinSuitableAudience::class, 'cabin_id', 'id');
    }

    protected static function boot()
    {
        parent::boot();
        
        static::deleted(function ($model) {
            $model->cabinPrices()->delete();
            $model->cabinRooms()->delete();
            $model->cabinAmenities()->delete();
            $model->cabinSuitableAudiences()->delete();
        });
    }
}
