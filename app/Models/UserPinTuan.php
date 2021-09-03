<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

/**
 * App\Models\UserPinTuan
 *
 * @property int $id
 * @property int|null $uid users表 -- id
 * @property string|null $operate_type 操作类型,充值：recharge，提现：withdrawal，消费：consumption
 * @property string $money 变动来拼金
 * @property string $money_before_change 变动前来拼金
 * @property string $order_no 充值订单号
 * @property int $status 充值状态:1处理中,2成功,3失败
 * @property string|null $remark 备注
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|UserPinTuan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserPinTuan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserPinTuan query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserPinTuan whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPinTuan whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPinTuan whereMoney($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPinTuan whereMoneyBeforeChange($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPinTuan whereOperateType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPinTuan whereOrderNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPinTuan whereRemark($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPinTuan whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPinTuan whereUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPinTuan whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class UserPinTuan extends Model
{
    use HasFactory;

    protected $table = 'user_lpj_log';
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
