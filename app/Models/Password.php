<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Password extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'password';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['password'];
}
