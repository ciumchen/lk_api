<?php

namespace App\Http\Controllers\API\Order;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\TradeOrder;
use App\Exceptions\LogicException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/*
 * 订单信息
 */

class TradeOrderController extends Controller
{

    /**生成订单信息
     * @param array $data
     * @param int $uid
     * @return array
     * @throws
     */
    public function setOrder(array $data, int $uid = 0)
    {
        $time = time();
        if ($uid == 0)
            throw new LogicException('请先登录');
        if (in_array($data['description'], ['HF', 'YK']))
        {
            $data['profitRatio'] = 0.05;
        } elseif ($data['description'] == 'MT')
        {
            $data['profitRatio'] = 0.1;
        } else
        {
            $data['profitRatio'] = 0;
        }

        $orderData = [
            'order_no' => $data['order_no'],
            'user_id' => $uid,
            'title' => $data['title'],
            'price' => $data['price'],
            'numeric' => $data['numeric'],
            'telecom' => $data['telecom'],
            'num' => $data['num'],
            'need_fee' => $data['need_fee'],
            'profit_price' => 0,
            'profit_ratio' => $data['profitRatio'],
            'pay_time' => $time,
            'status' => $data['status'],
            'order_from' => $data['order_from'],
            'integral' => 0,
            'description' => $data['description'],
            'oid' => $data['oid'],
            'remarks' => $data['remarks'],
            'created_at' => date("Y-m-d H:i:s"),
            'modified_time' => date("Y-m-d H:i:s")
        ];
        $tradeOrder = new TradeOrder();
        $res = $tradeOrder->setOrder($orderData);
        if ($res)
        {
            return ['code' => 1, 'msg' => '下单成功'];
        } else
        {
            return ['code' => 0, 'msg' => '下单失败'];
        }
    }
}
