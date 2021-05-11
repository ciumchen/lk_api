<?php

namespace App\Http\Controllers\api\Test;

use App\Http\Controllers\Controller;
use App\Libs\Yuntong\YuntongPay;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Class YuntongController 云通支付接口测试类
 * @package App\Http\Controllers\api\Test
 */
class YuntongController extends Controller
{


    //
    public function index()
    {
    }

    /**
     * 订单支付接口测试
     * @param Request $request
     */
    public function pay(Request $request)
    {
        $goods_title = $request->goods_title;
        $goods_desc = $request->goods_desc;
        $order_id = $request->order_id;
        $return_url = url('/api/yttest4');
        $aa = new YuntongPay();
        try {
            $bb = $aa
                ->setGoodsTitle($goods_title)
                ->setGoodsDesc($goods_desc)
                ->setAmount(0.6)
                ->setOrderId($order_id)
                ->setNotifyUrl($return_url)
                ->setType('alipay')
                ->setMethod('wap')
                ->pay();
            $response = json_decode($bb, true);
            /*判断返回更新订单*/
//array:6 [
//    "return_type" => "url"
//  "create_time" => "2021-05-10 11:24:46"
//  "sys_order_id" => "20210510112446348A37CD207ED"
//  "order_id" => "order_no_1"
//  "pay_url" => "http://order.foceplay.com/o/payment/20210510112446348A37CD207ED"
//  "status" => "pending"
//]
            response()->json($response);
        } catch (\Exception $e) {
            dd($e->getMessage());
        }
    }

    /**
     * 订单状态主动查询接口测试
     */
    public function order_status()
    {
        try {
            $Pay = new YuntongPay();
            $res = $Pay->OrderQuery('order_no_1');
            dump($res);
            $res = json_decode($res, true);
//array:9 [
//    "amount" => 0.6
//  "create_time" => "2021-05-10 11:24:46"
//  "sys_order_id" => "20210510112446348A37CD207ED"
//  "goods_title" => "商品标题"
//  "goods_desc" => "商品描述"
//  "app_id" => "app_2ac357bae1ce441397"
//  "order_id" => "order_no_1"
//  "pay_time" => "2021-05-10 11:48:06"
//  "status" => "success"
//]
            dd($res);
        } catch (\Exception $e) {
            dd($e->getMessage());
        }
    }

    /**
     * 订单退款测试
     */
    public function order_refund()
    {
        try {
            $Pay = new YuntongPay();
            $res = $Pay->OrderRefund('order_no_1');
            dump($res);
            $res = json_decode($res, true);
//array:8 [
//    "refun_amount" => 0.6
//  "refund_status" => "pending"
//  "sys_order_id" => "20210510112446348A37CD207ED"
//  "create_time" => "2021-05-10 11:24:46"
//  "refund_time" => "2021-05-10 16:11:30"
//  "order_id" => "order_no_1"
//  "status" => "pending"
//  "pay_time" => "2021-05-10 11:48:06"
//]
            dd($res);
        } catch (\Exception $e) {
            dd($e->getMessage());
        }
    }

    /**
     * 异步通知测试
     * @param Request $request
     */
    public function notify(Request $request)
    {
        try {
            if ($request->isMethod('get')) {
                Log::debug('YuntongPay_notify_get.log', [serialize($request->all())]);
            } elseif ($request->isMethod('post')) {
                Log::debug('YuntongPay_notify_post.log', [serialize($request->all())]);
            } else {
                Log::debug('YuntongPay_notify_else.log', [$request->getMethod()]);
            }
            $Pay = new YuntongPay();
            $data = $Pay->Notify();
            Log::debug('YuntongPay_notify.log', [serialize($data)]);
        } catch (\Exception $e) {
            Log::debug('YuntongPay_notify_error.log', [serialize($e)]);
        }
    }


}
