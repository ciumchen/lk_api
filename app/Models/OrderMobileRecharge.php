<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class OrderMobileRecharge
 *
 * @property int    id
 * @property string mobile
 * @property float  money
 * @property int    order_id
 * @property int    create_type
 * @property string order_no
 * @property string created_at
 * @property string updated_at
 * @property string trade_no
 * @property string status
 * @property string pay_status
 * @property string goods_title
 * @property int    uid
 * @package App\Models
 */
class OrderMobileRecharge extends Model
{
    
    use HasFactory;
    
    protected $table = 'order_mobile_recharge';
    
    /**
     * 创建代充订单
     *
     * @param int    $order_id order表ID
     * @param string $order_no 订单号
     * @param int    $uid      用户ID
     * @param string $mobile   手机号
     * @param float  $money    充值金额
     *
     * @return \App\Models\OrderMobileRecharge
     * @throws \Exception
     */
    public function setDlMobileOrder($order_id, $order_no, $uid, $mobile, $money)
    {
        try {
            $this->mobile = $mobile;
            $this->money = $money;
            $this->create_type = '2';
            $this->order_id = $order_id;
            $this->order_no = $order_no;
            $this->uid = $uid;
            $this->save();
        } catch (Exception $e) {
            throw  $e;
        }
        return $this;
    }
}
