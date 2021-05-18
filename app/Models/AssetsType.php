<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetsType extends Model
{
    protected $table = 'assets_type';

    const DEFAULT_ASSETS_NAME = 'usdt';
    const DEFAULT_ASSETS_ENCOURAGE = 'encourage';

    const CAN_WITHDRAW = 1;
    const CANT_WITHDRAW = 2;

    const CAN_RECHARGE = 1;
    const CANT_RECHARGE = 2;
}
