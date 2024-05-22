<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Report extends Model
{
    use HasFactory, Notifiable;

    protected $table = 'report';
    protected $primaryKey = 'report_id';


    protected $fillable = [
        'report_description', 'report_id_user', 'report_licence_plate', 'report_photo'
    ];


}
