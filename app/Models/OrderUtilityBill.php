<?php

namespace App\Models;

/**
 * TODO:水电煤订单数据操作
 */

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class OrderUtilityBill
 *
 * @property int    id
 * @property string order_no
 * @property int    user_id
 * @property string account
 * @property int    order_id
 * @property float  money
 * @property string trade_no
 * @property int    pay_status
 * @property int    status
 * @property string goods_title
 * @property int    create_type
 * @property string created_at
 * @property string updated_at
 * @package App\Models
 * @method static \Illuminate\Database\Eloquent\Builder|OrderUtilityBill newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderUtilityBill newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderUtilityBill query()
 * @mixin \Eloquent
 */
class OrderUtilityBill extends Model
{
    
    use HasFactory;
    
    /**
     * 生成水费账单
     *
     * @param string $account
     * @param float  $money
     * @param string $order_no
     * @param int    $order_id
     * @param int    $uid
     *
     * @return $this
     * @throws \Exception
     */
    public function setWaterOrder($account, $money, $order_no, $order_id, $uid)
    {
        try {
            $this->account = $account;
            $this->money = $money;
            $this->create_type = '1';
            $this->order_id = $order_id;
            $this->order_no = $order_no;
            $this->user_id = $uid;
            $this->save();
        } catch (Exception $e) {
            throw  $e;
        }
        return $this;
    }
    
    /**
     * 生成电费订单
     *
     * @param $account
     * @param $money
     * @param $order_no
     * @param $order_id
     * @param $uid
     *
     * @return $this
     * @throws \Exception
     */
    public function setElectricityOrder($account, $money, $order_no, $order_id, $uid)
    {
        try {
            $this->create_type = '2';
            $this->account = $account;
            $this->money = $money;
            $this->order_id = $order_id;
            $this->order_no = $order_no;
            $this->user_id = $uid;
            $this->save();
        } catch (Exception $e) {
            throw  $e;
        }
        return $this;
    }
    
    /**
     * 生成燃气费订单
     *
     * @param $account
     * @param $money
     * @param $order_no
     * @param $order_id
     * @param $uid
     *
     * @return $this
     * @throws \Exception
     */
    public function setGasOrder($account, $money, $order_no, $order_id, $uid)
    {
        try {
            $this->order_no = $order_no;
            $this->create_type = '3';
            $this->account = $account;
            $this->money = $money;
            $this->order_id = $order_id;
            $this->user_id = $uid;
            $this->save();
        } catch (Exception $e) {
            throw  $e;
        }
        return $this;
    }
}
