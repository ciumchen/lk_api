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
            return response()->json($response);
        } catch (\Exception $e) {
            dd($e->getMessage());
        }
    }

    /**
     * 订单状态主动查询接口测试
     * @param Request $request
     */
    public function order_status(Request $request)
    {
        $order_id = $request->order_id;
        try {
            $Pay = new YuntongPay();
            $res = $Pay->OrderQuery($order_id);
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
     * @param Request $request
     */
    public function order_refund(Request $request)
    {
        $order_id = $request->order_id;
        try {
            $Pay = new YuntongPay();
            $res = $Pay->OrderRefund($order_id);
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
            $data = $request->getContent();
            Log::debug('YuntongPay_notify_post.log', ['getContent=' . $data]);
//            $str = "a:8:{s:6:\"amount\";d:0.6;s:12:\"sys_order_id\";s:27:\"202105111537588934BF8862BFC\";s:11:\"create_time\";s:19:\"2021-05-11 15:37:58\";s:4:\"sign\";s:32:\"EA78C696FA3F54D98084A4D90A193450\";s:4:\"type\";s:15:\"payment.success\";s:8:\"order_id\";s:10:\"order_no_3\";s:6:\"app_id\";s:22:\"app_2ac357bae1ce441397\";s:8:\"pay_time\";s:19:\"2021-05-11 15:39:30\";}";
//            $data = unserialize($str);
//            $Pay = new YuntongPay();
//            $res = $Pay->Notify($data);
//            dump($res);
//            dd($data);
//            Log::debug('YuntongPay_notify.log', [serialize($data)]);
        } catch (\Exception $e) {
//            throw $e;
//            Log::debug('YuntongPay_notify_error.log', [serialize($e)]);
        }
        try {
            $data = $request->all();
            Log::debug('YuntongPay_notify_post.log', ['->all=' . $data]);
        } catch (\Exception $e) {
        }
        try {
            $data = $request->all();
            Log::debug('YuntongPay_notify_post.log', ['json_encode' . json_encode($data)]);
        } catch (\Exception $e) {
        }
    }


}
