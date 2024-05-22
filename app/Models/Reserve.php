<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class Reserve extends Model
{
    use HasFactory, Notifiable;
    use SoftDeletes;


    protected $table = 'reserve';
    protected $primaryKey = 'reserve_id';


    protected $fillable = [
        'reserve_id_spot', 'reserve_id_user', 'reserve_id_car', 'reserve_for_date'
    ];

    public function spot(){
        return $this->belongsTo('App\Models\Parking_spot','reserve_id_spot','parking_spot_id');
    }
    public function car(){
        return $this->belongsTo('App\Models\Car','reserve_id_car','car_id');
    }

    public function parking_lot(){
        return $this->hasOneThrough('App\Models\Parking_lot','App\Models\Parking_spot','parking_spot_id_parking_lot', 'parking_lot_id', 'reserve_id_spot', 'parking_spot_id_parking_lot');
    }


}
