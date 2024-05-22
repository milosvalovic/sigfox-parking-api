<?php


namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{

    protected $table = 'notification';
    protected $primaryKey = 'notification_id';

    protected $fillable = [
        'notification_firebase_token', 'notification_id_parking_lot'
    ];

    public function parking_lot()
    {
        return $this->belongsTo('App\Models\Parking_lot','notification_id_parking_lot','parking_lot_id');
    }

    public function parking_spots()
    {
        return $this->hasMany('App\Models\Parking_spot', "parking_spot_id_parking_lot", 'notification_id_parking_lot');
    }

    public function parking_spot(){
        return $this->hasOneThrough('App\Models\Parking_spot','App\Models\Parking_lot','parking_spot_id_parking_lot', 'parking_spot_id', 'notification_id_paring_lot', 'parking_spot_id_parking_lot');
    }
}
