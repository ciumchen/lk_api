<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Exceptions\LogicException;

class TradeOrder extends Model
{

    use HasFactory;

    protected $table = 'trade_order';

    /**
     * 生成订单号
     * @return string
     */
    public function CreateOrderNo()
    {
        return "PY_" . date("YmdHis") . rand(100000, 999999);
    }

    /**生成订单
     * @param array $data
     * @return bool
     * @throws
     */
    public function setOrder(array $data)
    {
        return DB::table($this->table)->insert($data);
    }

    /**检查是否已经支付
     * @param int $uid
     * @return array
     * @throws
     */
    public function checkOrderPay(int $uid)
    {
        $res = DB::table($this->table)->where([['user_id', '=', $uid], ['status', '=', 'await']])->get();
        if ( !$res)
            throw new LogicException('请先下单');
        if (count($res) > 0) {
            return ['code' => 1, 'msg' => '订单待支付'];
        } else {
            return ['code' => -1, 'msg' => '订单已支付'];
        }
    }

    /**更新订单状态
     * @param array $data
     * @throws
     */
    public function upTradeOrder(array $data)
    {
        $orderInfo = DB::table('trade_order')->where('order_no', '=', $data[ 'order_no' ])->get()->toArray();
        $order = [];
        foreach ($orderInfo as $val) {
            $order = $val;
        }
        $orderData = get_object_vars($order);
        dd($orderData);
        if ( !$orderInfo)
            throw new LogicException('订单不存在');
        if ($orderData[ 'status' ] != 'await')
            throw new LogicException('订单已支付');
        $orderIntegral = 0;
        //计算让利金额
        $profitPrice = $orderData[ 'need_fee' ] * $orderData[ 'profit_ratio' ];
        try {
            if ($data[ 'status' ] == 'succeeded' && !in_array($orderData[ 'status' ], ['pending', 'succeeded'])) {
                //更新订单表
                $upData = [
                    'status'       => $data[ 'status' ],
                    'profit_price' => $profitPrice,
                    'integral'     => $orderIntegral,
                    'pay_time'     => $data[ 'pay_time' ],
                    'end_time'     => $data[ 'end_time' ],
                ];
                DB::table('trade_order')->where('order_no', $data[ 'order_no' ])->update($upData);
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**获取用户信息
     * @param string $orderNo
     * @return array
     * @throws
     */
    public function userInfo(string $orderNo)
    {
        return DB::table($this->table)->where('order_no', $orderNo)->get()->toArray();
    }

    /**获取订单信息
     * @param string $orderNo
     * @return mixed
     * @throws
     */
    public function tradeOrderInfo(string $orderNo)
    {
        return DB::table($this->table)->where('order_no', $orderNo)->first();
    }

    /**获取用户信息
     * @param string $orderNo
     * @return array
     * @throws
     */
    public function getUser(string $orderNo)
    {
        return DB::table($this->table)->join('users', function ($join) use ($orderNo) {
            $join->on('trade_order.user_id', '=', 'users.id')
                ->where(['trade_order.order_no' => $orderNo, 'users.status' => 1]);
        })->get()->first();
    }

    /** 支付失败再次支付
     * @param string $oid
     * @return mixed
     * @throws
     */
    public function getOrderInfo(string $oid)
    {
        return DB::table($this->table)->where('oid', $oid)->first();
    }


}
