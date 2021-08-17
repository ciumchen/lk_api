<?php

namespace App\Http\Controllers\API\User;

use App\Exceptions\LogicException;
use App\Http\Controllers\Controller;
use App\Libs\Yuntong\YuntongPay;
use App\Models\Assets;
use App\Models\AssetsLogs;
use App\Models\ConvertLogs;
use App\Models\Order;
use App\Models\OrderMobileRecharge;
use App\Models\Setting;
use App\Models\TradeOrder;
use App\Models\UserPinTuan;
use App\Models\Users;
use App\Models\UserShoppingCardDhLog;
use App\Services\bmapi\MobileRechargeService;
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

    //查询用户的来70%usdt可兑换余额
    public function getUserDataUsdtYE(Request $request){
        $uid = $request->input('uid');
        $amount = Assets::where('uid',$uid)->where('assets_type_id',3)->value('amount');
        if ($amount){
            return response()->json(['code' => 1, 'msg' => array('usdt_amount'=>$amount)]);
        }else{
            return response()->json(['code' => 0, 'msg' => '用户uid资产不存在']);
        }
    }

    //使用70%usdt补贴金充值来拼金
    public function UserUsdtDhLpj(ReUserPinTuan $request){
        $user = $request->user();
        $ip = $request->input('ip');
        $money = $request->input('money');
        //查询70%usdt
        $userAssets = Assets::where('uid',$user->id)->where('assets_type_id',3)->first();
        if ($userAssets->amount>=$money){
            $oldAmount = $userAssets->amount;
            $order_no = createOrderNo();
            DB::beginTransaction();
            try {
                //扣除70%usdt和添加资产变动记录
                $userAssets->amount = $oldAmount-$money;
                $userAssets->save();

                $data = array(
                    'assets_type_id' => 3,
                    'assets_name' => 'usdt',
                    'uid' => $user->id,
                    'operate_type' => 'recharge_lpj',
                    'amount' => $money,
                    'amount_before_change' => $oldAmount,
                    'order_no' => $order_no,
                    'ip' => $ip,
                    'remark' => '兑换来拼金',
                    'user_agent' => 'recharge_lpj',
                );
                AssetsLogs::create($data);
                //更新用户来拼金额度和来拼金记录
                $user->balance_tuan = $user->balance_tuan+$money;
                $user->save();

                $data = array(
                    'uid' => $user->id,
                    'operate_type' => 'recharge',
                    'money' => $money,
                    'money_before_change' => $oldAmount,
                    'order_no' => $order_no,
                    'remark' => '兑换来拼金',
                    'status' => 2,
                );
                UserPinTuan::create($data);

                DB::commit();
                return response()->json(['code' => 1, 'msg' => '补贴金兑换成功']);
            } catch (Exception $e) {
                DB::rollBack();
                return response()->json(['code' => 0, 'msg' => '补贴金兑换失败']);
            }
        }else{
            return response()->json(['code' => 0, 'msg' => '补贴金余额不足']);
        }


    }

    //购买来拼金
    public function UserBuyLpj(ReUserPinTuan $request)
    {
        $user = $request->user();
        $ip = $request->input('ip');
        $money = $request->input('money');

        $order_no = createOrderNo();
        //创建充值记录
        $data = array(
            'uid' => $user->id,
            'operate_type' => 'recharge',
            'money' => $money,
            'money_before_change' => $user->balance_tuan,
            'order_no' => $order_no,
            'remark' => '支付宝充值',
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

    //购买来拼金支付回调
    public function getUserBuyLpjHd(Request $request)
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

    //购物卡兑换代充话费
    public function ShoppingCardDhDchf(Request $request){
        $user = $request->user();
        $ip = $request->input('ip');
        $money = $request->input('money');
        $mobile = $request->input('mobile');

        $money = 0.01;

        $reg = '/^1[3456789]\d{9}$/';
        if (preg_match($reg, $mobile) < 1) {
            throw new LogicException('手机号格式不正确');
        }
//        if (!in_array($money, [50, 100, 200])) {
//            throw new LogicException('话费充值金额不在可选值范围内');
//        }
        //查询用户购物卡余额
        if ($user->gather_card < $money){
            return response()->json(['code' => 0, 'msg' => '购物卡余额不足']);
        }
//        dd($user->toArray());

        DB::beginTransaction();
        try
        {
            //生成order录单
            $order_no = createOrderNo();
            $profit_ratio = Setting::where('key','set_business_rebate_scale_zl')->value('value');//代充让利比例
            $date = date("Y-m-d H:i:s",time());
            $profit_price = $money*$profit_ratio/100;
            $integral = floor($money*0.25*100)/100;

            $arr = array(
                'uid'          => $user->id,
                'business_uid' => 2,
                'profit_ratio' => $profit_ratio,
                'price'        => $money,
                'profit_price' => $profit_price,
                'name'         => '代充',
                'created_at'   => $date,
                'status'       => '1',
                'state'        => '1',
                'pay_status'   => 'await',
                'remark'       => '',
                'order_no'     => $order_no,
            );
            $orderData = Order::create($arr);

            //生成购物卡兑换订单
            $dataLog = array(
                'uid' => $user->id,
                'operate_type' => 'exchange_dc',
                'money' => $money,
                'money_before_change' => $user->gather_card,
                'order_no' => $order_no,
                'remark' => '代充',
            );
            UserShoppingCardDhLog::create($dataLog);


            //调用代充支付

            //接收话费支付回调，更改购买卡余额和记录、审核订单通过、添加排队

            //新增充值记录
            $re = (new MobileRechargeService)->addMobileOrder($order_no, $user->id, $mobile, $money,$orderData->id);
//            dd($re);
            //调用话费充值
            (new MobileRechargeService)->convertRecharge($order_no);



        } catch (Exception $e)
        {
            throw $e;
            DB::rollBack();
        }
        DB::commit();
        return json_encode(['code' => 200, 'msg' => '兑换话费充值成功']);





        //调用代充话费接口充值话费

        //获取代充回调结果

        //添加购物卡消费记录

        //扣除购物卡余额



    }


    /**usdt 兑换
     * @param array $data
     * @return mixed
     * @throws
     */
    public function commonConvert(array $data)
    {
        //插入数据到兑换记录
        (new ConvertLogs())->setConvert($data);

        //插入数据到变动记录
        (new ConvertLogs())->setAssetsLogs($data);

        //更新用户资产数据
        (new ConvertLogs())->updAssets($data);

        //order 表增加订单记录
        $ratio = Setting::getSetting('set_business_rebate_scale_cl');
        $profitPrice = $data['price'] * $ratio / 100;
        $order = (new Order())->setOrderSelf($data['uid'], 2, $ratio, $data['price'], $profitPrice,
            $data['orderNo'], $data['orderName'], 1, 'await', 'convert');
        //更新convert_logs 表 oid 字段
        (new ConvertLogs)->updOid($order->order_no, $order->id);

        //更新order 表审核状态
        (new OrderService())->completeBmOrder($data['orderNo']);
    }
}
