<?php

namespace App\Http\Controllers\API\Payment;

use App\Http\Controllers\Controller;
use App\Services\OrderService;
use Illuminate\Support\Facades\Auth;
use App\Models\PayLogs;
use App\Models\TradeOrder;
use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Exceptions\LogicException;
use App\Http\Controllers\API\Order\RechargeController;

/*
 * 支付异步回调通知
 */

class NotifyController extends Controller
{
    public function callBack(Request $request)
    {
        //获取json 数据
        $jsonData = $request->all();
        $json_data = json_decode($jsonData['data'],1);
        if(!empty($json_data))
        {
            Log::debug("adaPay notify info:\r\n".json_encode($json_data));
        } else
        {
            Log::debug("adaPay notify fail:参数为空");
        }

        $tradeOrder = new TradeOrder();
        $order = new Order();

        //检查支付金额与订单金额是否一致
        $tOrderData = $tradeOrder->tradeOrderInfo($json_data['order_no']);
        if ($tOrderData->price != $json_data['pay_amt'])
            throw new LogicException('支付金额与订单金额不一致');

        if($json_data['status'] == 'succeeded')
        {
            $userInfo = $tradeOrder->userInfo($json_data['order_no']);
            $user = [];
            foreach ($userInfo as $val)
            {
                $user = $val;
            }
            $userData = get_object_vars($user);
            //组装交易数据
            $payData = [
                'pid' => $json_data['id'],
                'uid' => $userData['user_id'],
                'order_no' => $json_data['order_no'],
                'pay_channel' => $json_data['pay_channel'],
                'pay_amt' => $json_data['pay_amt'],
                'description' => $json_data['description'],
                'party_order_id' => $json_data['party_order_id'],
                'out_trans_id' => $json_data['out_trans_id'],
                'status' => $json_data['status'],
                'created_time' => $json_data['created_time'],
                'end_time' => $json_data['end_time'],
            ];

            //更新订单表数据
            $tradeOrderData = [
                'order_no' => $json_data['order_no'],
                'status' => $json_data['status'],
                'uid' => $userData['user_id'],
                'pay_time' => $json_data['created_time'],
                'end_time' => $json_data['end_time'],
                'modified_time' => date("Y-m-d H:i:s"),
            ];

            $tradeData = (new TradeOrder())->tradeOrderInfo($json_data['order_no']);

            //组装话费数据
            $callData = [
                'numeric' => $tradeData->numeric,
                'price' => $json_data['pay_amt'],
                'order_no' => $json_data['order_no'],
            ];

            //组装加油卡数据
            $gasData = [
                'order_no' => $json_data['order_no'],
                'price' => $json_data['pay_amt'],
                'game_userid' => $tradeData->numeric,
            ];

            try {
                //插入支付记录
                $payLogs = new PayLogs();
                $payLogs->setPay($payData);

                //更新订单状态
                $tradeOrder->upTradeOrder($tradeOrderData);

                //自动充值
                if ($json_data['description'] == "HF")
                {
                    (new RechargeController())->setCall($callData);
                } elseif ($json_data['description'] == "YK")
                {
                    (new RechargeController())->setGas($gasData);
                } elseif ($json_data['description'] == "ZL")
                {
                    (new RechargeController())->callDefray($callData);
                }

                //更新 order 表审核状态
                (new OrderService())->completeOrder($json_data['order_no']);

                //发送录单消息通知
                (new Order())->orderMsg($json_data['order_no']);


            } catch (\Exception $e) {
                //记录错误日志
                Log::error("adaPay notify fail:".$e->getMessage());
                return "fail";
            }
            return "succeed";
        } else
        {
            return "fail";
        }
    }
}
