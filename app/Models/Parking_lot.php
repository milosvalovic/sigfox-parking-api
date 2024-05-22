<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Parking_lot extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'parking_lot';
    protected $primaryKey = 'parking_lot_id';

    protected $fillable = [
        'parking_lot_name', 'parking_lot_lat', 'parking_lot_lng', 'parking_lot_city', 'parking_lot_street', 'parking_lot_street_number', 'parking_lot_id_partner', 'parking_lot_capacity', "parking_lot_description"
    ];


    public function parking_spot()
    {
        return $this->hasMany('App\Models\Parking_spot','parking_spot_id_parking_lot','parking_lot_id');
    }

    public function partner(){
        return $this->belongsTo(Partner::class,'parking_lot_id_partner',"partner_id");
    }


}
