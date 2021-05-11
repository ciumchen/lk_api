<?php

namespace App\Http\Controllers\API\Payment;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaymentRequest;
use Illuminate\Http\Request;
use App\Exceptions\LogicException;
use Exception;
use App\Models\TradeOrder;
use App\Models\Order;
use App\Http\Controllers\API\Order\TradeOrderController;
use Illuminate\Support\Facades\Log;

/*
 * 调用Adapay 第三方支付接口
 */

//加载支付SDK需要的文件
include_once "../common/AdapaySdk/init.php";
include_once "../app/Http/Controllers/API/Payment/config.php";

class AdaPayController extends Controller
{
    const appId = "app_c91b40ca-af1c-4eaa-a7dc-99bc39febe18";
    //const notify = "https://lk.catspawvideo.com/api/notify"; //正式环境
    const notify = "http://112.124.9.185:8081/api/notify"; //测试环境

    /**
     * 调用支付
     * @param PaymentRequest $request
     * @throws
     */
    public function CreatePay(Request $request)
    {
        //初始化支付类
        $paymentInit = new \AdaPaySdk\Payment();

        //组装支付对象参数
        $paymentData = $request->all();
        $uid = $paymentData['uid'] ?: 0;
        /*if ($uid == 0)
            throw new LogicException('请先登录');*/

        $totalFee = $paymentData['money'] * $paymentData['number'];
        $tradeOrder = new TradeOrder();

        //检查用户当月消费金额
        $sumData = [
            'uid' => $uid,
            'description' => $paymentData['description']
        ];
        $totalPrice = $tradeOrder->getMonthSum($sumData);
        if ($paymentData['description'] == 'HF' && $totalPrice > 500)
        {
            throw new LogicException('本月话费充值金额已达上限');
        } elseif ($paymentData['description'] == 'YK' && $totalPrice > 2000)
        {
            throw new LogicException('本月油卡充值金额已达上限');
        }

        $status = $tradeOrder->checkOrderPay($uid);
        $orderFrom = '';

        $orderNo = "PY_". date("YmdHis").rand(100000, 999999);
        $payment = [
            'app_id' => self::appId,
            'order_no' => $orderNo,
            'pay_channel' => $paymentData['payChannel'],
            'pay_amt' => sprintf("%.2f", $totalFee),
            'goods_title' => $paymentData['goodsTitle'],
            'goods_desc' => $paymentData['goodsDesc'],
            'description' => $paymentData['description'],
            'device_info' => $paymentData['deviceInfo'],
            'notify_url' => self::notify,
        ];

        if ($paymentData['payChannel'] == 'alipay')
        {
            $orderFrom = 'alipay';
        } elseif ($paymentData['payChannel'] == 'wx')
        {
            $orderFrom = 'wx';
        }

        //订单数据组装
        $orderData = [
            'order_no' => $orderNo,
            'user_id' => $uid,
            'numeric' => $paymentData['numeric'],
            'telecom' => $paymentData['telecom'],
            'title' => $paymentData['goodsTitle'],
            'price' => $paymentData['money'],
            'num' => $paymentData['number'],
            'description' => $paymentData['description'],
            'pay_time' => time(),
            'end_time' => time(),
            'modified_time' => date("Y-m-d H:i:s"),
            'status' => 'await',
            'remarks' => '',
            'order_from' => $orderFrom,
            'need_fee' => sprintf("%.2f", $totalFee),
            'created_at' => date("Y-m-d H:i:s")
        ];

        //组装 order 表订单数据
        $date = date("Y-m-d H:i:s");
        $name = '';
        switch ($paymentData['description'])
        {
            case "MT":
                $name = '美团';
                break;
            case "HF":
                $name = '话费';
                break;
            case "YK":
                $name = '油卡';
                break;
        }
        if (in_array($paymentData['description'], ['HF', 'YK']))
        {
            $profit_ratio = 5;
        } else
        {
            $profit_ratio = 10;
        }

        $profit_price = $paymentData['money'] * ($profit_ratio / 100);

        $orderParam = [
            'uid' => $uid,
            'business_uid' => 2,
            'name' => $name,
            'profit_ratio' => $profit_ratio,
            'price' => $paymentData['money'],
            'profit_price' => sprintf("%.2f", $profit_price),
            'pay_status' => 'await',
            'created_at' => $date,
            'updated_at' => $date,
        ];

        //美团卡备注姓名
        if ($paymentData['description'] == 'MT')
        {
            $orderData['remarks'] = $paymentData['name'];
        } elseif ($paymentData['description'] == 'YK')
        {
            //油卡备注手机号
            $orderData['remarks'] = $paymentData['telephone'];
        }

        if ($paymentData['description'] == 'LR')
        {
            $orderData['oid'] = $paymentData['orderId'];
        }

        try {
            $tradeOrder = new TradeOrderController();
            $Order = new Order();

            //创建订单
            if (in_array($paymentData['description'], ['HF', 'YK', 'MT']))
            {
                $oid = $Order->setOrder($orderParam);
                $orderData['oid'] = $oid;
            }

            //创建订单
            $tradeOrder->setOrder($orderData, $uid);

            if ($status['code'] == 1)
            {
                //发起支付
                $paymentInit->create($payment);
                $resPay = json_decode($paymentInit->result[1], 1);
                $payData = json_decode($resPay['data'], 1);
                return ['url' => $payData['expend']['pay_info']];
            }

        } catch (Exception $e) {
            throw $e;
        }
    }

    /** 支付失败再次支付
     * @param Request $request
     * @return array
     * @throws
     */
    public function againPay(Request $request)
    {
        $orderNo = "PY_". date("YmdHis").rand(100000, 999999);
        $data = $request->all();

        $tradeOrder = new TradeOrder();
        $trade = new TradeOrderController();
        //初始化支付类
        $paymentInit = new \AdaPaySdk\Payment();

        $tradeData = $tradeOrder->getOrderInfo($data['oid']);
        if (in_array($tradeData->status, ['pending', 'succeeded']))
            throw new LogicException('订单不属于未支付或支付失败状态');

        //组装支付数据
        $payment = [
            'app_id' => self::appId,
            'order_no' => $orderNo,
            'pay_channel' => $data['payChannel'],
            'pay_amt' => $tradeData->price,
            'goods_title' => $tradeData->title,
            'goods_desc' => $data['goodsDesc'],
            'description' => $tradeData->description,
            'device_info' => $data['deviceInfo'],
            'notify_url' => self::notify,
        ];

        $orderFrom = '';
        if ($data['payChannel'] == 'alipay')
        {
            $orderFrom = 'alipay';
        } elseif ($data['payChannel'] == 'wx')
        {
            $orderFrom = 'wx';
        }

        //订单数据组装
        $orderData = [
            'order_no' => $orderNo,
            'user_id' => $tradeData->user_id,
            'numeric' => $tradeData->numeric,
            'telecom' => $tradeData->telecom,
            'title' => $tradeData->title,
            'price' => $tradeData->price,
            'num' => $tradeData->num,
            'description' => $tradeData->description,
            'pay_time' => time(),
            'end_time' => time(),
            'modified_time' => date("Y-m-d H:i:s"),
            'status' => 'await',
            'order_from' => $orderFrom,
            'need_fee' => $tradeData->need_fee,
            'oid' => $data['oid'],
            'remarks' => $tradeData->remarks,
            'created_at' => date("Y-m-d H:i:s")
        ];

        //创建订单
        $trade->setOrder($orderData, $tradeData->user_id);

        try {
            //发起支付
            $paymentInit->create($payment);
            $resPay = json_decode($paymentInit->result[1], 1);
            $payData = json_decode($resPay['data'], 1);
            return ['url' => $payData['expend']['pay_info']];

        } catch (Exception $e) {
            throw $e;
        }
    }
}
