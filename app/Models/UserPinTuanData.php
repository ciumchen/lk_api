<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

/**
 * App\Models\UserPinTuanData
 *
 * @property int $id
 * @property int|null $uid users表 -- id
 * @property string|null $operate_type 操作类型,兑换代充：exchange_dc,批量代充：exchange_pl,兑换美团：exchange_mt
 * @property string $money 变动购物卡金额
 * @property string $money_before_change 变动前购物卡余额
 * @property string $order_no 充值订单号
 * @property int $status 兑换状态:1处理中,2成功,3失败
 * @property string|null $remark 备注
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $gather_shopping_card_id gather_shopping_card表的id
 * @method static \Illuminate\Database\Eloquent\Builder|UserPinTuanData newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserPinTuanData newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserPinTuanData query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserPinTuanData whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPinTuanData whereGatherShoppingCardId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPinTuanData whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPinTuanData whereMoney($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPinTuanData whereMoneyBeforeChange($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPinTuanData whereOperateType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPinTuanData whereOrderNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPinTuanData whereRemark($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPinTuanData whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPinTuanData whereUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPinTuanData whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class UserPinTuanData extends Model
{
    use HasFactory;

    protected $table = 'user_shopping_card_dh_log';
    protected $fillable = [
        'uid',
        'operate_type',
        'money',
        'money_before_change',
        'order_no',
        'status',
        'remark',
        'created_at',
        'updated_at',
    ];
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format($this->dateFormat ?: 'Y-m-d H:i:s');
    }





}
