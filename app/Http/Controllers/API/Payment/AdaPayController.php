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
use Illuminate\Support\Facades\DB;
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
    const notify = "http://112.124.9.185:8081/api/notify";

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

        if ($paymentData['description'] == 'LR')
        {
            $orderData['oid'] = $paymentData['orderId'];
        }

        try {
            $tradeOrder = new TradeOrderController();
            $Order = new Order();

            //创建订单
            $oid = $Order->setOrder($orderParam);
            $orderData['oid'] = $oid;
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
}
