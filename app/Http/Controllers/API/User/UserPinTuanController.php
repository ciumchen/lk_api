<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use App\Libs\Yuntong\YuntongPay;
use App\Models\Order;
use App\Models\UserPinTuan;
use App\Models\Users;
use App\Services\OrderService;
use App\Services\OrderTwoService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\API\Payment\YuntongPayController;
use App\Http\Requests\UserPinTuan as ReUserPinTuan;

class UserPinTuanController extends Controller
{
    //查询用户的来拼金
    public function getUserDataLpj(Request $request){
        $uid = $request->input('uid');
        $balance_tuan = Users::where('id',$uid)->value('balance_tuan');
        if ($balance_tuan){
            return response()->json(['code' => 1, 'msg' => array('balance_tuan'=>$balance_tuan)]);
        }else{
            return response()->json(['code' => 0, 'msg' => '用户uid不存在']);
        }
    }

    //使用70%usdt补贴金充值来拼金
    public function UserUsdtDhLpj(Request $request){
        $user = $request->user();
        $ip = $request->input('ip');
        $money = $request->input('money');
        $ip != '' ?: $ip = '183.14.29.143';

    }





    //购买来拼金
    public function UserBuyLpj(Request $request)
    {
        $user = $request->user();
        $ip = $request->input('ip');
        $money = $request->input('money');
        $ip != '' ?: $ip = '183.14.29.143';

        $order_no = createOrderNo();
        //创建充值记录
        $data = array(
            'uid' => $user->id,
            'operate_type' => 'recharge',
            'money' => $money,
            'money_before_change' => $user->balance_tuan,
            'order_no' => $order_no,
        );
        $lpjLog = UserPinTuan::create($data);

//        dd($lpjLog);

        //调用支付宝支付
        $payModel = new YuntongPayController();
        $data = [
            'goodsTitle' => '充值来拼金',
            'goodsDesc' => '充值来拼金',//商品描述
            'need_fee' => $money,//消费金额
            'order_no' => $order_no,//订单号
            'order_from' => 'alipay',//支付渠道 固定值：alipay|wx|unionpay
            'ip' => $ip,//ip
//            'return_url' => "http://ning.catspawvideo.com/api/getLkMemberPayHd",
            'return_url' => "",
        ];
        return $payModel->payRequest($data, createNotifyUrl('api/getUserBuyLpjHd'));

    }

    //购买会员支付回调
    public function getUserBuyLpjHd(UserPinTuan $request)
    {
        $Pay = new YuntongPay();
        $json = $request->getContent();
        DB::beginTransaction();
        try {
            $data = json_decode($json, true);
            $res = $Pay->Notify($data);
//            Log::info("=======打印充值来拼金支付回调数据====1======", $data);
            if (!empty($res)) {
//                Log::info("=======打印充值来拼金支付回调数据====2======", $data);
                if ($data['type'] == "payment.success") {
                    $lpjLog = UserPinTuan::where('order_no', $data['order_id'])->first();
                    if ($lpjLog != null) {
                        //修改充值记录状态
                        $lpjLog->status = 2;
                        $lpjLog->save();
                        //修改用户来拼金
                        $userInfo = Users::where('id', $lpjLog->uid)->first();
                        $userInfo->balance_tuan = $userInfo->balance_tuan + $data['amount'];
                        $userInfo->save();
                    } else {
                        Log::info("=======打印充值来拼金支付回调数据=====保存数据错误1=====", $data);
                    }
                } else {
                    Log::info("=======打印充值来拼金支付回调数据=====保存数据错误2=====", $data);
                }

            } else {
                Log::info("=======打印充值来拼金支付回调数据=====解析为空=====");
                throw new Exception('解析为空');
            }
            DB::commit();
            $Pay->Notify_success();
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug('YuntongNotify-打印充值来拼金支付回调数据-验证不通过-getUserBuyLpjHd-' . $e->getMessage(), [$json . '---------' . json_encode($e)]);
            $Pay->Notify_failed();
        }

    }


}
