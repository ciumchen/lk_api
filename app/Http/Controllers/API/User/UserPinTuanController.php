<?php

namespace App\Http\Controllers\API\User;

use App\Exceptions\LogicException;
use App\Http\Controllers\API\Order\RechargeController;
use App\Http\Controllers\Controller;
use App\Http\Requests\GwkDhDefaultAuthRequest;
use App\Http\Requests\GwkDhMtAuthRequest;
use App\Libs\Yuntong\YuntongPay;
use App\Models\Assets;
use App\Models\AssetsLogs;
use App\Models\ConvertLogs;
use App\Models\GatherShoppingCard;
use App\Models\GwkZfOperationLog;
use App\Models\Order;
use App\Models\OrderMobileRecharge;
use App\Models\Setting;
use App\Models\TradeOrder;
use App\Models\User;
use App\Models\UserPinTuan;
use App\Models\Users;
use App\Models\UserShoppingCardDhLog;
use App\Services\bmapi\MobileRechargeService;
use App\Services\OrderService;
use App\Services\UserGatherService;
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
        if (!$user->id) {
            return response()->json(['code' => 0, 'msg' => '用户信息错误']);
        }
        $ip = $request->input('ip');
        $money = $request->input('money');
        //查询70%usdt
        $userAssets = Assets::where('uid', $user->id)->where('assets_type_id', 3)->first();
        if ($userAssets->amount >= $money) {
            $oldAmount = $userAssets->amount;
            $order_no = createOrderNo();
            $oldLpj = $user->balance_tuan;//变动前来拼金余额
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
                    'amount_before_change' => $oldLpj,
                    'order_no' => $order_no,
                    'ip' => $ip,
                    'remark' => '补贴金兑换来拼金',
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
                    'money_before_change' => $oldLpj,
                    'order_no' => $order_no,
                    'remark' => '补贴金兑换来拼金',
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
        if (!$user->id) {
            return response()->json(['code' => 0, 'msg' => '用户信息错误']);
        }
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
//        Log::debug("=========购物卡兑换接收参数打印=================",$request->all());

        $user = $request->user();
        if (!$user->id) {
            return response()->json(['code' => 0, 'msg' => '用户信息错误']);
        }
//        $ip = $request->input('ip');
        $money = $request->input('money');
        $mobile = $request->input('mobile');
        $type = $request->input('type');
        if ($money=='' || $mobile=='' || $type==''){
            return response()->json(['code' => 0, 'msg' => '参数不能为空']);
        }

        if ($type=='LR'){
            $profit_ratio = $request->input('profit_ratio');//接收录单的让利比例
            if (in_array($profit_ratio,array(5,10,20)) == false){
                return response()->json(['code' => 0, 'msg' => '让利比例不合法']);
            }
        }

        $reg = '/^1[3456789]\d{9}$/';
        if (preg_match($reg, $mobile) < 1) {
            throw new LogicException('手机号格式不正确');
        }
        if ($mobile==$user->phone){
            return response()->json(['code' => 0, 'msg' => '自己不能给自己录单']);
        }
        $mobileUserData = User::where('phone',$mobile)->first();
        if ($mobileUserData==null){
            return response()->json(['code' => 0, 'msg' => '录单手机号用户不存在']);
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
                $profit_ratio = Setting::where('key', 'set_business_rebate_scale_hf')->value('value');//话费让利比例
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
                $profit_ratio = Setting::where('key', 'set_business_rebate_scale_zl')->value('value');//代充让利比例
                break;
            default:
                return response()->json(['code' => 0, 'msg' => '兑换类型错误']);
                break;

        }


        DB::beginTransaction();
        try {
            //生成order录单
            $order_no = createOrderNo();
            $date = date("Y-m-d H:i:s", time());
            $profit_price = $money * $profit_ratio / 100;
            $integralArr = array(
                5 => 0.25,
                10 => 0.5,
                20 => 1,
            );

            if ($type == "LR") {
                //查询用户购物卡余额和录单实际让利金额
                if ($user->gather_card < $profit_price) {
                    return response()->json(['code' => 0, 'msg' => '购物卡余额不足']);
                }
                $orderUid = Users::where('phone', $mobile)->value('id');
                $business_uid = $user->id;
            } else {
                //查询用户购物卡余额
                if ($user->gather_card < $money) {
                    return response()->json(['code' => 0, 'msg' => '购物卡余额不足']);
                }
                $orderUid = $user->id;
                $business_uid = 2;
            }

            if (intval($orderUid)<=0){
                return response()->json(['code' => 0, 'msg' => '用户不存在']);
            }

            $arr = array(
                'uid' => $orderUid,
                'business_uid' => $business_uid,
                'profit_ratio' => $profit_ratio,
                'price' => $money,
                'profit_price' => $profit_price,
                'name' => $name,
                'created_at' => date("Y-m-d H:i:s", time()),
                'updated_at' => date("Y-m-d H:i:s", time()),
                'status' => '1',
                'state' => '1',
//                'pay_status' => 'succeeded',
                'remark' => '',
                'order_no' => $order_no,
                'description' => $description,
                'payment_method' => 'gwk',
            );
            $orderData = Order::create($arr);
            $orderId = $orderData->id;

            //创建购物卡处理记录
            (new GwkZfOperationLog())->CreateGwkClLog($orderId,$order_no);

            //创建TradeOrder表记录
            $arr = array(
                'user_id' => $user->id,
                'title' => $title,
                'telecom' => $telecom,
                'price' => $money,
                'num' => 1,
                'numeric' => $mobile,
                'status' => "await",
                'order_from' => 'gwk',
                'order_no' => $order_no,
                'need_fee' => $money,
                'profit_ratio' => $profit_ratio / 100,
                'profit_price' => $profit_price,
                'integral' => $money * $integralArr[$profit_ratio],
                'description' => $description,
                'oid' => $orderId,
                'pay_time' => date("Y-m-d H:i:s", time()),
                'modified_time' => date("Y-m-d H:i:s", time()),
                'created_at' => date("Y-m-d H:i:s", time()),
                'updated_at' => date("Y-m-d H:i:s", time()),

            );
            TradeOrder::create($arr);

            //生成购物卡兑换订单
            if ($type=="LR"){
                //购物卡兑换金额为录单的实际让利金额
                $money = $profit_price;
            }
            //创建gather_shopping_card购物卡金额变动记录
            $cardArr = array(
                'uid' => $user->id,
                'money' => $money,
                'type' => 2,
                'name' => $typeName,
                'created_at' => date("Y-m-d H:i:s", time()),
                'updated_at' => date("Y-m-d H:i:s", time()),
            );
            $gwkLogModel = new GatherShoppingCard();
            $reGscId = $gwkLogModel->create($cardArr)->id;

            $dataLog = array(
                'uid' => $user->id,
                'operate_type' => $operate_type,
                'money' => $money,
                'money_before_change' => $user->gather_card,
                'order_no' => $order_no,
                'remark' => $remark,
                'status' => 1,
                'gather_shopping_card_id' => $reGscId,
                'created_at' => date("Y-m-d H:i:s", time()),
                'updated_at' => date("Y-m-d H:i:s", time()),
            );
            UserShoppingCardDhLog::create($dataLog);
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug("===========ShoppingCardDhDefault===购物卡兑换-录单、话费直充、和代充==生成订单异常================",[$e->getMessage()]);
            return response()->json(['code' => 0, 'msg' => '订单信息错误']);
            return false;
//            throw $e;
        }
        DB::commit();

        return response()->json(['code' => 1, 'data' => array('msg' => "订单创建成功", 'order_no' => $order_no, 'oid' => $orderId, 'type' => $type)]);

    }

    //购物卡生成订单后调用支付,购物卡兑换话费支付回调
    public function gwkDhCallback(GwkDhDefaultAuthRequest $request)
    {
        $user = $request->user();
        if (!$user->id) {
            return response()->json(['code' => 0, 'msg' => '用户信息错误']);
        }
//        $ip = $request->input('ip');
        $order_no = $request->input('order_no');
        $money = $request->input('money');
        $mobile = $request->input('mobile');
        $type = $request->input('type');
        $oid = $request->input('oid');

        $password = $request->input('password');
        //验证支付密码
        $result = (new UserGatherService())->checkProvingCardPwd(array("uid"=>$user->id,"password"=>$password));
        if ($result!=200){
            return $result;
        }

        switch ($type) {
            case "LR":
                $create_type = 11;
                $typeName = "兑换录单";
                if ($user->phone == $mobile) {
                    return response()->json(['code' => 0, 'msg' => '自己不能给自己录单']);
                }
                break;
            case "HF":
                $create_type = 1;
                $typeName = "兑换话费";
                break;
            case "ZL":
                $create_type = 2;
                $typeName = "兑换代充";
                break;
            default:
                return response()->json(['code' => 0, 'msg' => '兑换类型错误']);
                break;

        }

        //查询购物卡兑换订单
        $orderData = Order::where(['id' => $oid, 'order_no' => $order_no, 'description' => $type, 'status' => 1])->first();
        if ($orderData != null) {
            DB::beginTransaction();
            try {
                $logData = GwkZfOperationLog::lockForUpdate()->where(['oid'=>$oid,'order_no' => $order_no,'status'=>1])->first();
                if ($logData==null){
                    return response()->json(['code' => 0, 'msg' => '订单非法处理']);
                }else{
                    $logData->status = 2;
                    $logData->save();
                }

                if ($orderData->price!=$money){
                    return response()->json(['code' => 0, 'msg' => '参数异常']);
                }
                //订单通过审核添加积分，更新order 表审核状态--添加资产记录10条,录单审核不排队，其他订单审核要排队
                $userInfo = Users::where('id', $user->id)->first();
                if ($type == 'LR') {//不排队
                    //扣除用户购物卡余额
                    if ($userInfo->gather_card < $orderData->profit_price){
                        return response()->json(['code' => 0, 'msg' => '购物卡余额不足']);
                    }
                    $userInfo->gather_card = $userInfo->gather_card - $orderData->profit_price;//购物卡金额减去录单的实际让利金额
                    $userInfo->save();
                    //审核订单添加积分，积分不排队
                    (new OrderService())->MemberUserOrder($oid, 'LR');
                } else {//排队
                    //扣除用户购物卡余额
                    if ($userInfo->gather_card < $orderData->price){
                        return response()->json(['code' => 0, 'msg' => '购物卡余额不足']);
                    }
                    $userInfo->gather_card = $userInfo->gather_card - $orderData->price;//购物卡减去消费金额
                    $userInfo->save();
                    //审核订单添加积分，积分要排队
                    (new OrderService())->addOrderIntegral($oid);
                }
                //修改状态
                $reData = Order::where(['id' => $oid, 'order_no' => $order_no])->first();
                $reData->pay_status = 'succeeded';
                $reData->save();

                $reData2 = TradeOrder::where(['oid' => $oid, 'order_no' => $order_no])->first();
                $reData2->status = 'succeeded';
                $reData2->save();

                $gwkDhLogData = UserShoppingCardDhLog::where('order_no', $order_no)->first();
                $gwkDhLogData->status = 2;
                $gwkDhLogData->save();

                $logData->status = 3;
                $logData->save();

                $gwkStatus = 1;
            } catch (Exception $e) {
                DB::rollBack();
                $gwkStatus = 2;
                return response()->json(['code' => 0, 'msg' => '订单信息错误']);
                return false;
//            throw $e;
            }
            DB::commit();

            //调用支付接口
            if ($gwkStatus === 1) {
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
                    (new MobileRechargeService)->addMobileOrder($order_no, $user->id, $mobile, $money, $oid);
                    //购物卡兑换代充
                    (new MobileRechargeService)->GwkConvertRecharge($order_no, $create_type);

                }

                return json_encode(['code' => 200, 'msg' => $typeName . '成功']);
            } else {
                return json_encode(['code' => 0, 'msg' => $typeName . '失败']);
            }
        } else {
            return json_encode(['code' => 0, 'msg' => $typeName . '订单不存在']);
        }

    }

    //购物卡兑换美团生成订单
    public function ShoppingCardDhMt(Request $request)
    {
        $user = $request->user();
        if (!$user->id) {
            return response()->json(['code' => 0, 'msg' => '用户信息错误']);
        }
//        $ip = $request->input('ip');
        $money = $request->input('money');
        $mobile = $request->input('mobile');
        $userName = $request->input('userName');

        if ($money=='' || $mobile=='' || $userName==''){
            return response()->json(['code' => 0, 'msg' => '参数不能为空']);
        }

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
                'created_at' => date("Y-m-d H:i:s", time()),
                'status' => '1',
                'state' => '1',
                'pay_status' => 'await',
                'remark' => '',
                'order_no' => $order_no,
                'description' => 'MT',
            );
            $orderData = Order::create($arr);
            $orderId = $orderData->id;

            //创建购物卡处理记录
            (new GwkZfOperationLog())->CreateGwkClLog($orderId,$order_no);

            //创建TradeOrder表记录
            $arr = array(
                'user_id' => $user->id,
                'title' => '美团卡充值',
                'telecom' => '美团卡',
                'price' => $money,
                'num' => 1,
                'numeric' => $mobile,
                'status' => "await",
                'order_from' => 'gwk',
                'order_no' => $order_no,
                'need_fee' => $money,
                'profit_ratio' => $profit_ratio / 100,
                'profit_price' => $profit_price,
                'integral' => $money * $integralArr[$profit_ratio],
                'description' => 'MT',
                'oid' => $orderId,
                'remarks' => $userName,
                'pay_time' => date("Y-m-d H:i:s", time()),
                'modified_time' => date("Y-m-d H:i:s", time()),
                'created_at' => date("Y-m-d H:i:s", time()),
                'updated_at' => date("Y-m-d H:i:s", time()),

            );
            TradeOrder::create($arr);


            //创建gather_shopping_card购物卡金额变动记录
            $mtArr = array(
                'uid' => $user->id,
                'money' => $money,
                'type' => 2,
                'name' => "兑换美团",
                'created_at' => date("Y-m-d H:i:s", time()),
                'updated_at' => date("Y-m-d H:i:s", time()),
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
                'status' => 1,
                'created_at' => date("Y-m-d H:i:s", time()),
                'updated_at' => date("Y-m-d H:i:s", time()),
            );
            UserShoppingCardDhLog::create($dataLog);

        } catch (Exception $e) {
            DB::rollBack();
//            throw $e;
            Log::debug("===========ShoppingCardDhMt===购物卡兑换美团生成订单异常================",[$e->getMessage()]);
            return response()->json(['code' => 0, 'msg' => '订单信息错误']);
        }
        DB::commit();

        return response()->json(['code' => 1, 'data' => array('msg' => "订单创建成功", 'order_no' => $order_no, 'oid' => $orderId)]);

    }

    //生成美团订单后确认兑换,不需要调用支付
    public function gwkDhCallbackNoZf(GwkDhMtAuthRequest $request)
    {
        $user = $request->user();
        if (!$user->id) {
            return response()->json(['code' => 0, 'msg' => '用户信息错误']);
        }
//        $ip = $request->input('ip');
        $order_no = $request->input('order_no');
        $money = $request->input('money');
        $mobile = $request->input('mobile');
//        $type = $request->input('type');
        $oid = $request->input('oid');

        $password = $request->input('password');
        //验证支付密码
        $result = (new UserGatherService())->checkProvingCardPwd(array("uid"=>$user->id,"password"=>$password));
        if ($result!=200){
            return $result;
        }

        //查询购物卡兑换订单
        $orderData = Order::where(['id' => $oid, 'order_no' => $order_no, 'status' => 1])->first();
        if ($orderData != null) {
            DB::beginTransaction();
            try {
                $logData = GwkZfOperationLog::lockForUpdate()->where(['oid'=>$oid,'order_no' => $order_no,'status'=>1])->first();
                if ($logData==null){
                    return response()->json(['code' => 0, 'msg' => '订单非法处理']);
                }else{
                    $logData->status = 2;
                    $logData->save();
                }
                //扣除用户购物卡余额
                $userInfo = Users::where('id', $user->id)->first();
                $userInfo->gather_card = $userInfo->gather_card - $money;
                $userInfo->save();

                //通过审核添加积分，更新order 表审核状态
                (new OrderService())->addOrderIntegral($oid);

                //修改order表、TradeOrder表、UserShoppingCardDhLog表状态
                $orderData->pay_status = "succeeded";
                $orderData->save();

                $traderOrderData = TradeOrder::where(['oid' => $oid, 'order_no' => $order_no])->first();
                $traderOrderData->status = 'succeeded';
                $traderOrderData->save();

                $gwkDhLogData = UserShoppingCardDhLog::where(['status' => 1, 'order_no' => $order_no])->first();
                $gwkDhLogData->status = 2;
                $gwkDhLogData->save();

                $logData->status = 3;
                $logData->save();

            } catch (Exception $e) {
                DB::rollBack();
                Log::debug("===========gwkDhCallbackNoZf===购物卡兑换美团生成--确认兑换异常================",[$e->getMessage()]);
                return response()->json(['code' => 0, 'msg' => '兑换美团失败']);
//            throw $e;
            }
            DB::commit();
            return json_encode(['code' => 1, 'msg' => '兑换美团成功']);
        } else {
            return json_encode(['code' => 0, 'msg' => '兑换美团失败']);
        }

    }


}
