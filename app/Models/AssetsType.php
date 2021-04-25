<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetsType extends Model
{
    protected $table = 'assets_type';

    const DEFAULT_ASSETS_NAME = 'iets';
    const DEFAULT_ASSETS_ENCOURAGE = 'encourage';
}
