<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Parking_spot extends Model
{
    use HasFactory;
    protected $table = 'parking_spot';
    protected $primaryKey = 'parking_spot_id';

    protected $fillable = [
        'parking_spot_occupied', 'parking_spot_id_parking_lot', "parking_spot_has_charger", "parking_spot_number"
    ];


    public function parking_lot()
    {
        return $this->belongsTo('App\Models\Parking_lot','parking_spot_id_parking_lot','parking_lot_id');
    }

    public function reservation(){
        return $this->hasMany('App\Models\Reserve','reserve_id_spot','parking_spot_id');
    }
}
