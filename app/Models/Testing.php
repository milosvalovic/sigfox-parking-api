<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Testing extends Model
{

    protected $table = 'testing';
    protected $primaryKey = 'testing_id';


    protected $fillable = [
        'testing_id_parking_spot', 'testing_spot_occupied', 'testing_created', 'testing_created_unixtime'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
}
