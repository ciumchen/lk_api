<?php

namespace App\Http\Controllers\API\Payment;

use App\Exceptions\LogicException;
use App\Http\Controllers\Controller;
use App\Libs\Yuntong\Config;
use App\Libs\Yuntong\YuntongPay;
use App\Models\AirTradeLogs;
use App\Models\Order;
use App\Models\PayLogs;
use App\Models\TradeOrder;
use App\Services\bmapi\MobileRechargeService;
use App\Services\OrderService;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\API\Order\RechargeController;
use App\Services\AirOrderService;

class YuntongNotifyController extends Controller
{
    /**
     * 雲通支付回調
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function callBack(Request $request)
    {
        $Pay = new YuntongPay();
        $json = $request->getContent();
        try {
            Log::debug('YunNotify入口数据', [$json]);
            $data = json_decode($json, true);
            $res = $Pay->Notify($data);
            if (!empty($res)) {
                Log::debug('YunNotify数据', [$json]);
                $this->updateOrderPaid($res);
            } else {
                Log::debug('YunNotify数据为空', [$json]);
                throw new Exception('解析为空');
            }
            $Pay->Notify_success();
        } catch (Exception $e) {
            Log::debug('YuntongNotify-验证不通过-'.$e->getMessage(), [$json.'---------'.json_encode($e)]);
            $Pay->Notify_failed();
        }
    }

    /**
     * 更新订单为已支付
     *
     * @param $data
     *
     * @throws LogicException
     */
    public function updateOrderPaid($data)
    {
        try {
            Log::debug('UpdateTrade订单数据$data', [json_encode($data)]);
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
            Log::debug('Trade订单数据$payData', [json_encode($payData)]);
            Log::debug('Trade订单数据$tradeData', [json_encode($tradeData)]);
            Log::debug('Trade订单数据$callData', [json_encode($callData)]);
            Log::debug('Trade订单数据$gasData', [json_encode($gasData)]);
            $payLogs = new PayLogs();
            $payLogs->setPay($payData);
            //更新订单状态
            $TradeOrder->upTradeOrder($tradeOrderData);
            //更新 order 表审核状态
            (new OrderService())->completeOrder($data[ 'order_id' ]);
            //自动充值
            if ($trade_order->description == "HF") {
                /* 原手机充值*/
                (new RechargeController())->setCall($callData);
                /* 斑马力方手机充值*/
//                (new MobileRechargeService())->recharge($trade_order->oid, $data[ 'order_id' ]);
            } elseif ($trade_order->description == "YK") {
                (new RechargeController())->setGas($gasData);
            } elseif ($trade_order->description == "ZL") {
                /* 原代充 */
//                (new RechargeController())->callDefray($callData);
                /* 斑马力方手机充值*/
                (new MobileRechargeService())->recharge($trade_order->oid, $data[ 'order_id' ]);
            } elseif ($trade_order->description == "LR") {
                //发送录单消息通知
                (new Order())->orderMsg($data[ 'order_id' ]);
            }
        } catch (\LogicException $le) {
            throw $le;
        }
    }

    /**机票支付回調
     *
     * @param  Request  $request
     */
    public function airPayNotify(Request $request)
    {
        $Pay = new YuntongPay();
        $json = $request->getContent();
        try {
            Log::debug('AirNotify入口数据', [$json]);
            $data = json_decode($json, true);
            $res = $Pay->Notify($data);
            if (!empty($res)) {
                Log::debug('AirNotify数据', [$json]);
                $this->updAirPay($res);
            } else {
                Log::debug('AirNotify数据为空', [$json]);
                throw new Exception('解析为空');
            }
            $Pay->Notify_success();
        } catch (Exception $e) {
            Log::debug('AirNotify-验证不通过-'.$e->getMessage(), [$json.'---------'.json_encode($e)]);
            $Pay->Notify_failed();
        }
    }

    /**更新机票信息
     *
     * @param $data
     *
     * @throws
     */
    public function updAirPay($data)
    {
        try {
            Log::debug('UpdateAirTrade订单数据$data', [json_encode($data)]);
            $orderInfo = (new Order())->getOrderInfo($data[ 'order_id' ]);
            $AirOrderInfo = (new AirTradeLogs())->getAirTradeInfo($data[ 'order_id' ]);
            if ($orderInfo->price != $data[ 'amount' ]) {
                throw new LogicException('支付金额与订单金额不一致');
            }
            if ($data[ 'type' ] != 'payment.success') {
                throw new LogicException('支付状态异常');
            }
            //组装交易数据
            $payData = [
                'pid'            => $data[ 'sys_order_id' ],
                'uid'            => $orderInfo[ 'uid' ],
                'order_no'       => $data[ 'order_id' ],
                'pay_channel'    => $AirOrderInfo->pay_channel,
                'pay_amt'        => $data[ 'amount' ],
                'description'    => 'AT',
                'party_order_id' => $data[ 'sys_order_id' ],
                'out_trans_id'   => $data[ 'sys_order_id' ],
                'status'         => 'succeeded',
                'created_time'   => $AirOrderInfo->created_at,
                'end_time'       => $data[ 'pay_time' ],
            ];
            Log::debug('airTrade订单数据$payData', [json_encode($payData)]);
            $payLogs = new PayLogs();
            $payLogs->setPay($payData);
            //更新 order 表支付状态
            (new Order())->updPay($data[ 'order_id' ]);
            //更新 order 表审核状态
            //(new OrderService())->completeBmOrder($data[ 'order_id' ]);
            //机票订单
            (new AirOrderService())->airOrder($data[ 'order_id' ]);
        } catch (\LogicException $le) {
            throw $le;
        }
    }

    /**
     * Description:
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @throws \Throwable
     * @author lidong<947714443@qq.com>
     * @date   2021/6/11 0011
     */
    public function bmPayCallback(Request $request)
    {
        $Pay = new YuntongPay();
        $json = $request->getContent();
        DB::beginTransaction();
        try {
            $data = json_decode($json, true);
            $res = $Pay->Notify($data);
            if (!empty($res)) {
                $Order = new Order();
                $orderInfo = $Order->getOrderByOrderNo($data[ 'order_id' ]);
                if ($orderInfo->price != $data[ 'amount' ]) {
                    throw new Exception('付款金额与应付金额不一致');
                }
                /* 更新订单表以及积分 */
                $OrderService = new OrderService();
                $description = $OrderService->getDescription($orderInfo->id);
                $OrderService->completeOrderTable($orderInfo->id, $orderInfo->uid, $description, $orderInfo->order_no);
                /* 更新对应斑马订单表 */
                $OrderService->updateSubOrder($orderInfo->id, $res, $description);
            } else {
                throw new Exception('解析为空');
            }
            DB::commit();
            /* 订单完成后续充值 */
            $OrderService->afterCompletedOrder($orderInfo->id, $res, $description, $orderInfo);
            $Pay->Notify_success();
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug('YuntongNotify-验证不通过-bmCallback-'.$e->getMessage(), [$json.'---------'.json_encode($e)]);
            $Pay->Notify_failed();
        }
    }
}
