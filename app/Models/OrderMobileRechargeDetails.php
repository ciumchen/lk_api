<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\OrderMobileRechargeDetails
 *
 * @property int                             $id
 * @property int                             $order_mobile_id order_mobile订单ID
 * @property int                             $order_id        order订单ID
 * @property string                          $mobile          充值手机
 * @property string                          $money           充值金额
 * @property int                             $status          充值状态:0充值中,1成功,9撤销
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static Builder|OrderMobileRechargeDetails newModelQuery()
 * @method static Builder|OrderMobileRechargeDetails newQuery()
 * @method static Builder|OrderMobileRechargeDetails query()
 * @method static Builder|OrderMobileRechargeDetails whereCreatedAt($value)
 * @method static Builder|OrderMobileRechargeDetails whereId($value)
 * @method static Builder|OrderMobileRechargeDetails whereMobile($value)
 * @method static Builder|OrderMobileRechargeDetails whereMoney($value)
 * @method static Builder|OrderMobileRechargeDetails whereOrderId($value)
 * @method static Builder|OrderMobileRechargeDetails whereOrderMobileId($value)
 * @method static Builder|OrderMobileRechargeDetails whereStatus($value)
 * @method static Builder|OrderMobileRechargeDetails whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class OrderMobileRechargeDetails extends Model
{
    use HasFactory;
    
    protected $table = 'order_mobile_recharge_details';
}
