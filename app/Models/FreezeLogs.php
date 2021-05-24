<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FreezeLogs extends Model
{
    protected $table = 'freeze_logs';
    protected $fillable = [
        'assets_type_id',
        'assets_name',
        'uid',
        'amount_before_change',
        'amount',
        'operate_type',
        'tx_hash',
        'ip',
        'user_agent',
        'remark',
    ];
    protected $appends = ['updated_date'];



    /**
     * 类型.
     */
    const OPERATE_TYPE_EXCHANGE_IETS = 'exchagne_iets';//兑换iets
    const OPERATE_TYPE_IETS_TO_USDT = 'IETS兑换为USDT';//兑换iets
    /**
     * 类型文本.
     *
     * @var array
     */
    public static $operateTypeTexts = [
        self::OPERATE_TYPE_EXCHANGE_IETS => '兑换',
        self::OPERATE_TYPE_IETS_TO_USDT => '转换为USDT',
    ];

    public function getUpdatedDateAttribute($value)
    {
//        dd($value);
        return date("Y-m-d H:i:s",strtotime($this->attributes['updated_at']));
    }

}
