<?php

namespace App\Models;

use App\Exceptions\LogicException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;


class UserUpdatePhoneLogSd extends Authenticatable
{

    use HasFactory, HasApiTokens, Notifiable;
    protected $table = 'user_update_phone_log_sd';

    protected $fillable = [
        'user_id',
        'time',
        'edit_to_phone',
    ];

}
