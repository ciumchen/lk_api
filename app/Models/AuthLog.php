<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuthLog extends Model
{
    const DEFAULT_STATUS = 1;
    const BY_STATUS = 2;
    const REFUSE_STATUS = 3;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uid',
        'id_card',
        'name',
        'id_card_img',
        'id_card_people_img',
    ];
}
