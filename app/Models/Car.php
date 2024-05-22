<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Car extends Model
{
    use HasFactory, Notifiable;

    protected $table = 'car';
    protected $primaryKey = 'car_id';


    protected $fillable = [
        'car_brand', 'car_model', 'car_licence_plate', 'car_color', 'car_year', 'car_id_user'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
}
