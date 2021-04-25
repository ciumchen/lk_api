<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IntegralLogs extends Model
{
    protected $table = 'integral_log';

    public static $typeLabel = [
        'consumption' => '消费增加',
        'Jangli' => '让利增加',
        'rebate' => '分红扣除积分',
        'spend' => '消费订单完成',
    ];

    public static $rolLabel = [
        1 => '普通积分',
        2 => '商家积分',
    ];
}
