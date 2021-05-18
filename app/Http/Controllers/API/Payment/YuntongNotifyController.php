<?php

namespace App\Http\Controllers\api\Payment;

use App\Exceptions\LogicException;
use App\Http\Controllers\Controller;
use App\Libs\Yuntong\YuntongPay;
use App\Models\Order;
use App\Models\PayLogs;
use App\Models\TradeOrder;
use App\Services\OrderService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\API\Order\RechargeController;

class YuntongNotifyController extends Controller
{

    //
    public function callBack(Request $request)
    {
        $Pay = new YuntongPay();
        $json = $request->getContent();
//        $json = "{\"amount\":50.00,\"sys_order_id\":\"202105181025040236DD7B1E8B1\",\"create_time\":\"2021-05-18 10:25:04\",\"sign\":\"65653ADD0E9BCEE2C47E7E7ED52FB6EA\",\"type\":\"payment.success\",\"order_id\":\"PY_20210518102503845710\",\"app_id\":\"app_2ac357bae1ce441397\",\"pay_time\":\"2021-05-18 10:25:32\"}";
        try {
            $data = json_decode($json, true);
            $res = $Pay->Notify($data);
            if (!empty($res)) {
                Log::debug('YuntongNotify订单更新', [$json]);
                $this->updateOrderPaid($res);
            } else {
                Log::debug('YuntongNotify解析为空', [$json]);
                throw new Exception('YuntongNotify解析为空');
            }
            $Pay->Notify_success();
        } catch (Exception $e) {
            Log::debug('YuntongNotify验证不通过', [$json . '---------' . json_encode($e)]);
            $Pay->Notify_failed();
//            throw $e;
        }
    }

    /**
     * 更新订单为已支付
     * @param $data
     * @throws LogicException
     */
    public function updateOrderPaid($data)
    {
//$data =array:8 [
//    "amount" => 0.6
//"sys_order_id" => "202105111537588934BF8862BFC"
//"create_time" => "2021-05-11 15:37:58"
//"sign" => "EA78C696FA3F54D98084A4D90A193450"
//"type" => "payment.success"
//"order_id" => "order_no_3"
//"app_id" => "app_2ac357bae1ce441397"
//"pay_time" => "2021-05-11 15:39:30"
//]
        try {
            $TradeOrder = new TradeOrder();
            $trade_order = $TradeOrder->tradeOrderInfo($data[ 'order_id' ]);
            if ($trade_order->price != $data[ 'amount' ]) {
                throw new LogicException('支付金额与订单金额不一致');
            }
            if ($data[ 'type' ] != 'payment.success') {
                throw new LogicException('支付状态异常');
            }
            $user_info = $TradeOrder->userInfo($data[ 'order_id' ]);
            $user = [];
            foreach ($user_info as $val) {
                $user = $val;
            }
            $userData = get_object_vars($user);
            //组装交易数据
            $payData = [
                'pid'            => $data[ 'sys_order_id' ],
                'uid'            => $userData[ 'user_id' ],
                'order_no'       => $data[ 'order_id' ],
                'pay_channel'    => $trade_order->order_from,
                'pay_amt'        => $data[ 'amount' ],
                'description'    => $trade_order->description,
                'party_order_id' => $data[ 'sys_order_id' ],
                'out_trans_id'   => $data[ 'sys_order_id' ],
                'status'         => 'succeeded',
                'created_time'   => $trade_order->created_at,
                'end_time'       => $data[ 'pay_time' ],
            ];
            //更新订单表数据
            $tradeOrderData = [
                'order_no'      => $data[ 'order_id' ],
                'status'        => 'succeeded',
                'uid'           => $userData[ 'user_id' ],
                'pay_time'      => $data[ 'pay_time' ],
                'end_time'      => $data[ 'pay_time' ],
                'modified_time' => date("Y-m-d H:i:s"),
            ];
            $tradeData = (new TradeOrder())->tradeOrderInfo($data[ 'order_id' ]);
            //组装话费数据
            $callData = [
                'numeric'  => $tradeData->numeric,
                'price'    => $data[ 'amount' ],
                'order_no' => $data[ 'order_id' ],
            ];
            //组装加油卡数据
            $gasData = [
                'order_no'    => $data[ 'order_id' ],
                'price'       => $data[ 'amount' ],
                'game_userid' => $tradeData->numeric,
            ];
            $payLogs = new PayLogs();
            $payLogs->setPay($payData);
            //更新订单状态
            $TradeOrder->upTradeOrder($tradeOrderData);
            //自动充值
            if ($trade_order->description == "HF") {
                (new RechargeController())->setCall($callData);
            } elseif ($trade_order->description == "YK") {
                (new RechargeController())->setGas($gasData);
            }
            //更新 order 表审核状态
            (new OrderService())->completeOrder($data[ 'order_id' ]);
        } catch (\LogicException $le) {
            dd($le);
            throw $le;
        }
    }
}
