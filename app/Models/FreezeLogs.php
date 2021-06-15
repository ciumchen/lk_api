<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\FreezeLogs
 *
 * @property int $id
 * @property int $assets_type_id 资产类型id
 * @property string $assets_name 资产名称
 * @property int $uid uid
 * @property string $operate_type 操作类型
 * @property string $amount 变动数量
 * @property string $amount_before_change 变动前数量
 * @property string|null $ip ip
 * @property string $user_agent ua
 * @property string|null $remark 备注
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $updated_date
 * @method static \Illuminate\Database\Eloquent\Builder|FreezeLogs newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FreezeLogs newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FreezeLogs query()
 * @method static \Illuminate\Database\Eloquent\Builder|FreezeLogs whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FreezeLogs whereAmountBeforeChange($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FreezeLogs whereAssetsName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FreezeLogs whereAssetsTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FreezeLogs whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FreezeLogs whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FreezeLogs whereIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FreezeLogs whereOperateType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FreezeLogs whereRemark($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FreezeLogs whereUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FreezeLogs whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FreezeLogs whereUserAgent($value)
 * @mixin \Eloquent
 */
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
