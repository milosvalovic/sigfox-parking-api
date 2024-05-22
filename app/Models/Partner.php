<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Partner extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = 'partner';
    protected $primaryKey = 'partner_id';
    protected $fillable = [
        'partner_name',
    ];


    protected $hidden = [
        'partner_id'
    ];

    public function parking_lot()
    {
        return $this->hasMany('App\Models\Parking_lot','parking_spot_id_parking_lot','parking_lot_id');
    }
}
