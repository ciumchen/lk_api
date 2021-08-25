<?php

namespace App\Http\Controllers\API\User;

use App\Exceptions\LogicException;
use App\Http\Controllers\API\Order\RechargeController;
use App\Http\Controllers\Controller;
use App\Libs\Yuntong\YuntongPay;
use App\Models\Assets;
use App\Models\AssetsLogs;
use App\Models\ConvertLogs;
use App\Models\GatherShoppingCard;
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
use Bmapi\Api\MobileRecharge\PayBill;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\API\Payment\YuntongPayController;
use App\Http\Requests\UserPinTuan as ReUserPinTuan;

class UserPinTuanController extends Controller
{
    //查询用户的来拼金
    public function getUserDataLpj(Request $request)
    {
        $uid = $request->input('uid');
        $balance_tuan = Users::where('id', $uid)->value('balance_tuan');
        if ($balance_tuan) {
            return response()->json(['code' => 1, 'msg' => array('balance_tuan' => $balance_tuan)]);
        } else {
            return response()->json(['code' => 0, 'msg' => '用户uid不存在']);
        }
    }

    //查询用户的来70%usdt可兑换余额
    public function getUserDataUsdtYE(Request $request)
    {
        $uid = $request->input('uid');
        $amount = Assets::where('uid', $uid)->where('assets_type_id', 3)->value('amount');
        if ($amount) {
            return response()->json(['code' => 1, 'msg' => array('usdt_amount' => $amount)]);
        } else {
            return response()->json(['code' => 0, 'msg' => '用户uid资产不存在']);
        }
    }

    //使用70%usdt补贴金充值来拼金
    public function UserUsdtDhLpj(ReUserPinTuan $request)
    {
        $user = $request->user();
        $ip = $request->input('ip');
        $money = $request->input('money');
        //查询70%usdt
        $userAssets = Assets::where('uid', $user->id)->where('assets_type_id', 3)->first();
        if ($userAssets->amount >= $money) {
            $oldAmount = $userAssets->amount;
            $order_no = createOrderNo();
            DB::beginTransaction();
            try {
                //扣除70%usdt和添加资产变动记录
                $userAssets->amount = $oldAmount - $money;
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
                $user->balance_tuan = $user->balance_tuan + $money;
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
        } else {
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

    //购物卡兑换-录单、话费直充、和代充
    public function ShoppingCardDhDefault(Request $request)
    {
        $user = $request->user();
        if (!$user->id){
            return response()->json(['code' => 0, 'msg' => '用户信息错误']);
        }
//        $ip = $request->input('ip');
        $money = $request->input('money');
        $mobile = $request->input('mobile');
        $type = $request->input('type');

        $reg = '/^1[3456789]\d{9}$/';
        if (preg_match($reg, $mobile) < 1) {
            throw new LogicException('手机号格式不正确');
        }
        switch ($type) {
            case "LR":
                $name = '录单';
                $description = "LR";
                $title = "订单录入";
                $telecom = "订单录入";
                $operate_type = "exchange_lr";
                $remark = "录单";
                $create_type = 11;
                $typeName = "兑换录单";
                break;
            case "HF":
                $name = '话费';
                $description = "HF";
                $title = "话费充值";
                $telecom = "话费充值";
                $operate_type = "exchange_hf";
                $remark = "话费";
                $create_type = 1;
                $typeName = "兑换话费";
                break;
            case "ZL":
                $name = '代充';
                $description = "ZL";
                $title = "话费代充";
                $telecom = "话费代充";
                $operate_type = "exchange_zl";
                $remark = "代充";
                $create_type = 2;
                $typeName = "兑换代充";
                break;
            default:
                return response()->json(['code' => 0, 'msg' => '兑换类型错误']);
                break;

        }

        //查询用户购物卡余额
        if ($user->gather_card < $money) {
            return response()->json(['code' => 0, 'msg' => '购物卡余额不足']);
        }
        DB::beginTransaction();
        try {
            //生成order录单
            $order_no = createOrderNo();
            $profit_ratio = Setting::where('key', 'set_business_rebate_scale_zl')->value('value');//代充让利比例
            $date = date("Y-m-d H:i:s", time());
            $profit_price = $money * $profit_ratio / 100;
            $integralArr = array(
                5 => 0.25,
                10 => 0.5,
                20 => 1,
            );

            if ($type=="LR"){
                $orderUid = Users::where('phone',$mobile)->value('id');
                $business_uid = $user->id;
            }else{
                $orderUid = $user->id;
                $business_uid = 2;
            }

            $arr = array(
                'uid' => $orderUid,
                'business_uid' => $business_uid,
                'profit_ratio' => $profit_ratio,
                'price' => $money,
                'profit_price' => $profit_price,
                'name' => $name,
                'created_at' => $date,
                'updated_at' => $date,
                'status' => '1',
                'state' => '1',
                'pay_status' => 'succeeded',
                'remark' => '',
                'order_no' => $order_no,
                'description' => $description,
            );
            $orderData = Order::create($arr);
            $orderId = $orderData->id;

            //创建TradeOrder表记录
            $arr = array(
                'user_id' => $user->id,
                'title' => $title,
                'telecom' => $telecom,
                'price' => $money,
                'num' => 1,
                'numeric' => $mobile,
                'status' => "succeeded",
                'order_from' => 'gwk',
                'order_no' => $order_no,
                'need_fee' => $money,
                'profit_ratio' => $profit_ratio / 100,
                'profit_price' => $profit_price,
                'integral' => $money * $integralArr[$profit_ratio],
                'description' => $description,
                'oid' => $orderId,
                'created_at' => $date,
                'updated_at' => $date,

            );
            TradeOrder::create($arr);

            //创建gather_shopping_card购物卡金额变动记录
            $cardArr = array(
                'uid'=>$user->id,
                'money'=>$money,
                'type'=>2,
                'name'=>$typeName,
                'created_at' => $date,
                'updated_at' => $date,
            );
            $gwkLogModel = new GatherShoppingCard();
            $reGscId = $gwkLogModel->create($cardArr)->id;

            //生成购物卡兑换订单
            $dataLog = array(
                'uid' => $user->id,
                'operate_type' => $operate_type,
                'money' => $money,
                'money_before_change' => $user->gather_card,
                'order_no' => $order_no,
                'remark' => $remark,
                'gather_shopping_card_id' => $reGscId,
                'created_at' => $date,
                'updated_at' => $date,
            );
            UserShoppingCardDhLog::create($dataLog);

            //扣除用户购物卡余额
            $userInfo = Users::where('id',$user->id)->first();
            $userInfo->gather_card = $userInfo->gather_card - $money;
            $userInfo->save();

            //订单通过审核添加积分，更新order 表审核状态--添加资产记录10条
            (new OrderService())->addOrderIntegral($orderId);
            $gwkStatus = 1;
        } catch (Exception $e) {
            $gwkStatus = 2;
            DB::rollBack();
            return false;
//            throw $e;
        }
        DB::commit();

        if ($gwkStatus===1) {
            if ($type == 'HF') {//兑换直充
                //组装话费数据
                $callData = [
                    'numeric' => $mobile,
                    'price' => $money,
                    'order_no' => $order_no,
                ];
                //调用话费充值
                (new RechargeController())->setCall($callData);
            } elseif ($type == 'ZL') {//兑换代充
                //新增充值记录
                (new MobileRechargeService)->addMobileOrder($order_no, $user->id, $mobile, $money, $orderId);
                //购物卡兑换代充
                (new MobileRechargeService)->GwkConvertRecharge($order_no, $create_type);

            } elseif ($type == 'LR') {//兑换录单
                if ($user->phone == $mobile) {
                    return response()->json(['code' => 0, 'msg' => '自己不能给自己录单']);
                }
                $dhLog = UserShoppingCardDhLog::where('order_no', $order_no)->first();
                $dhLog->status = 2;
                $dhLog->save();
            }
            return json_encode(['code' => 200, 'msg' => $typeName.'成功']);
        }else{
            return json_encode(['code' => 0, 'msg' => $typeName.'失败']);
        }



    }

    //购物卡兑换话费支付回调
    public function gwkDhHfHd(Request $request)
    {
        $data = $request->all();
        $MobileRecharge = new OrderMobileRecharge();
        $ShoppingModel = new UserShoppingCardDhLog();
        try {
            if (empty($data)) {
                throw new Exception('手机充值回调数据为空');
            }
            $PayBill = new PayBill();
            if (!$PayBill->checkSign($data)) {
                throw new Exception('验签不通过');
            }
            //单号充值
            $rechargeInfo = $MobileRecharge->where('order_no', $data['outer_tid'])
                ->first();
            if (empty($rechargeInfo)) {
                throw new Exception('未查询到订单数据');
            }
            //更新充值记录表数据
            if (!empty($rechargeInfo)) {
                $rechargeInfo->status = $data['recharge_state'];
                $rechargeInfo->trade_no = $data['tid'];
                $rechargeInfo->updated_at = $data['timestamp'];
                $rechargeInfo->save();
            }
            //更新兑换记录数据
            $ShoppingInfo = $ShoppingModel->where('order_no', $data['outer_tid'])
                ->first();
            if (empty($ShoppingInfo)) {
                throw new Exception('未查询到兑换数据');
            }
            if (!empty($ShoppingInfo)) {
                switch ($data['recharge_state']) {
                    case 0:
                        $status = 3;
                        break;
                    case 1:
                        $status = 2;
                        break;
                    case 9:
                        $status = 3;
                        break;
                    default:
                        $status = 3;
                        break;
                }
                $ShoppingInfo->status = $status;
                $ShoppingInfo->updated_at = $data['timestamp'];
                $ShoppingInfo->save();

            }
        } catch (Exception $e) {
            Log::debug('gwkDhHfHd-Error:' . $e->getMessage(), [json_encode($data)]);
            throw $e;
        }
    }

    //购物卡兑换美团
    public function ShoppingCardDhMt(Request $request)
    {
        $user = $request->user();
        if (!$user->id){
            return response()->json(['code' => 0, 'msg' => '用户信息错误']);
        }
//        $ip = $request->input('ip');
        $money = $request->input('money');
        $mobile = $request->input('mobile');
        $userName = $request->input('userName');

        $reg = '/^1[3456789]\d{9}$/';
        if (preg_match($reg, $mobile) < 1) {
            throw new LogicException('手机号格式不正确');
        }

        //查询用户购物卡余额
        if ($user->gather_card < $money) {
            return response()->json(['code' => 0, 'msg' => '购物卡余额不足']);
        }
        DB::beginTransaction();
        try {
            //生成order录单
            $order_no = createOrderNo();
            $profit_ratio = Setting::where('key', 'set_business_rebate_scale_mt')->value('value');//美团让利比例
            $date = date("Y-m-d H:i:s", time());
            $profit_price = $money * $profit_ratio / 100;
            $integralArr = array(
                5 => 0.25,
                10 => 0.5,
                20 => 1,
            );

            $arr = array(
                'uid' => $user->id,
                'business_uid' => 2,
                'profit_ratio' => $profit_ratio,
                'price' => $money,
                'profit_price' => $profit_price,
                'name' => '美团',
                'created_at' => $date,
                'status' => '1',
                'state' => '1',
                'pay_status' => 'succeeded',
                'remark' => '',
                'order_no' => $order_no,
                'description' => 'MT',
            );
            $orderData = Order::create($arr);
            $orderId = $orderData->id;

            //创建TradeOrder表记录
            $arr = array(
                'user_id' => $user->id,
                'title' => '美团卡充值',
                'telecom' => '美团卡',
                'price' => $money,
                'num' => 1,
                'numeric' => $mobile,
                'status' => "succeeded",
                'order_from' => 'gwk',
                'order_no' => $order_no,
                'need_fee' => $money,
                'profit_ratio' => $profit_ratio / 100,
                'profit_price' => $profit_price,
                'integral' => $money * $integralArr[$profit_ratio],
                'description' => 'MT',
                'oid' => $orderId,
                'remarks' => $userName,
                'created_at' => date("Y-m-d H:i:s", time()),
                'updated_at' => date("Y-m-d H:i:s", time()),

            );
            TradeOrder::create($arr);


            //创建gather_shopping_card购物卡金额变动记录
//            $gwkLogModel = new GatherShoppingCard();
//            $gwkLogModel->uid = $user->id;
//            $gwkLogModel->money = $money;
//            $gwkLogModel->type = 2;
//            $gwkLogModel->name = "兑换美团";
//            $gwkLogModel->save();

            $mtArr = array(
                'uid'=>$user->id,
                'money'=>$money,
                'type'=>2,
                'name'=>"兑换美团",
                'created_at' => $date,
                'updated_at' => $date,
            );
            $gwkLogModel = new GatherShoppingCard();
            $reGscId = $gwkLogModel->create($mtArr)->id;

            //生成购物卡兑换订单
            $dataLog = array(
                'uid' => $user->id,
                'operate_type' => 'exchange_mt',
                'money' => $money,
                'money_before_change' => $user->gather_card,
                'order_no' => $order_no,
                'remark' => '美团',
                'gather_shopping_card_id' => $reGscId,
                'status' => 2,
                'created_at' => $date,
                'updated_at' => $date,
            );
            UserShoppingCardDhLog::create($dataLog);

            //扣除用户购物卡余额
            $userInfo = Users::where('id',$user->id)->first();
            $userInfo->gather_card = $userInfo->gather_card - $money;
            $userInfo->save();

            //通过审核添加积分，更新order 表审核状态
            (new OrderService())->addOrderIntegral($orderId);

        } catch (Exception $e) {
            throw $e;
            DB::rollBack();
        }
        DB::commit();
        return json_encode(['code' => 200, 'msg' => '兑换美团成功']);

    }


}
