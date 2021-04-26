<?php

namespace App\Http\Controllers\API\Payment;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\PayLogs;
use App\Models\TradeOrder;
use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Setting;
use App\Models\IntegralLog;

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

            $userIntegral = 0;
            $shopIntegral = 0;

            //计算用户积分
            if (in_array($json_data['description'], ['HF', 'YK']))
            {
                $userIntegral = $json_data['pay_amt'] * 0.25;
            } elseif (in_array($json_data['description'], ['MT']))
            {
                $userIntegral = $json_data['pay_amt'] * 0.5;
            } elseif (in_array($json_data['description'], ['LR']))
            {
                $orders = $order->getShop($json_data['order_no']);
                if ($orders['profit_ratio'] == 20)
                {
                    $userIntegral = $orders['price'];
                } elseif($orders['profit_ratio'] == 10)
                {
                    $userIntegral = $orders['price'] * 0.5;
                } else
                {
                    $userIntegral = $orders['price'] * 0.25;
                }
            }

            //计算商家积分
            if (in_array($json_data['description'], ['HF', 'YK']))
            {
                $shopIntegral = 5;
            } elseif (in_array($json_data['description'], ['MT']))
            {
                $shopIntegral = 10;
            }elseif (in_array($json_data['description'], ['LR']))
            {
                $orders = $order->getShop($json_data['order_no']);
                $shopIntegral = $orders['price'] * ($orders['profit_ratio'] / 100);
            }

            $orderData = [
                'userIntegral' => $userIntegral,
                'shopIntegral' => $shopIntegral,
                'order_no' => $json_data['order_no'],
            ];

            try {
                //插入支付记录
                $payLogs = new PayLogs();
                $payLogs->setPay($payData);

                //更新订单状态
                $tradeOrder->upTradeOrder($tradeOrderData);

                //更新 order 表审核状态
                $order->upOrder($json_data['order_no']);

                //更新用户积分
                if ($orderData)
                {
                    $order->upUsers($orderData);
                }

                $res = DB::table('order')->where('uid', $userData['user_id'])->first()->toArray();
                Log::info('=============', $res);
                $this->getPast($res['status'], $userData['user_id']);

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

    /**自动审核用户订单
     * @param string $status
     * @param int $uid
     * @return array
     * @throws
     */
    public function getPast(string $status, int $uid)
    {
        DB::beginTransaction();
        try{
            $order = Order::lockForUpdate()->find($uid);
            $order->status = $status;

            //用户应返还几分比例
            $userRebateScale = Setting::getManySetting('user_rebate_scale');
            $businessRebateScale = Setting::getManySetting('business_rebate_scale');
            $rebateScale = array_combine($businessRebateScale, $userRebateScale);

            if($status == 2)
            {
                //通过，给用户加积分、更新LK
                $customer = User::lockForUpdate()->find($order->uid);
                //按比例计算实际获得积分
                $customerIntegral = bcmul($order->price, bcdiv($rebateScale[(int)$order->profit_ratio],100, 4), 2);
                $amountBeforeChange =  $customer->integral;
                $customer->integral = bcadd($customer->integral, $customerIntegral,2);

                $lkPer = Setting::getSetting('lk_per')??300;
                //更新LK
                $customer->lk = bcdiv($customer->integral, $lkPer,0);
                $customer->save();
                IntegralLog::addLog($customer->id, $customerIntegral, IntegralLog::TYPE_SPEND, $amountBeforeChange, 1, '消费者完成订单');
                //给商家加积分，更新LK
                $business = User::lockForUpdate()->find($order->business_uid);
                $amountBeforeChange = $business->business_integral;
                $business->business_integral = bcadd($business->business_integral, $order->profit_price,2);

                $businessLkPer = Setting::getSetting('business_Lk_per')??60;
                //更新LK
                $business->business_lk = bcdiv($business->business_integral, $businessLkPer,0);
                $business->save();

                IntegralLog::addLog($business->id, $order->profit_price, IntegralLog::TYPE_SPEND, $amountBeforeChange, 2, '商家完成订单');
                //返佣
                $this->encourage($order, $customer, $business);
            } else
            {
                return ['code' => 0, 'msg' => '非已支付成功订单，不能通过审核'];
            }
            $order->save();

            DB::commit();
        }catch (\Exception $exception)
        {
            DB::rollBack();
            return $this->error($exception->getMessage());
        }
        return ['code' => 1, 'msg' => '审核通过'];
    }
}
