<?php

namespace App\Http\Controllers\API\Payment;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaymentRequest;
use Illuminate\Http\Request;
use App\Exceptions\LogicException;
use Exception;
use App\Models\TradeOrder;
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
    const notify = "https://lk.catspawvideo.com/api/notify";

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
            'order_from' => $orderFrom,
            'need_fee' => sprintf("%.2f", $totalFee),
            'oid' => 0,
            'created_at' => date("Y-m-d H:i:s")
        ];

        if ($paymentData['description'] == 'LR')
        {
            $orderData['oid'] = $paymentData['orderId'];
        }

        try {
            //创建订单
            $tradeOrder = new TradeOrderController();
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
}
