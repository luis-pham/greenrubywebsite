<?php
namespace Modules\BackEnd\Entities;

use Illuminate\Database\Eloquent\Builder;

class AppCruiseItinerary extends BaseModelMultiPrimaryKey
{
    protected $table = 'app_cruise_itinerary';
    protected $primaryKey = ['cruise_id', 'itinerary_id'];
    protected $fillable = [
        'cruise_id',
        'itinerary_id',
        'start_at'
    ];
    protected $hidden = [];
    public $timestamps = false;

    public function cruise()
    {
        return $this->belongsTo(AppCruise::class, 'cruise_id', 'id'); // adjust 'id' if your PK is different
    }

    // Relationship to the Itinerary model
    public function itinerary()
    {
        return $this->belongsTo(AppItinerary::class, 'itinerary_id', 'id');
    }

    public function scopeReservable(Builder $query): Builder{
        return $query->where('start_at', '>', date('Y-m-d'));
    }
}
