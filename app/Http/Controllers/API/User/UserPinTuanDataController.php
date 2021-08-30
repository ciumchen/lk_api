<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use App\Libs\Yuntong\YuntongPay;
use App\Models\Assets;
use App\Models\AssetsLogs;
use App\Models\GatherShoppingCard;
use App\Models\GwkZfOperationLog;
use App\Models\Order;
use App\Models\TradeOrder;
use App\Models\UserPinTuan;
use App\Models\Users;
use App\Models\UserShoppingCardDhLog;
use App\Services\OrderService;
use App\Services\OrderTwoService;
use App\Services\UserGatherService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\API\Payment\YuntongPayController;
use App\Http\Requests\UserPinTuan as ReUserPinTuan;

class UserPinTuanDataController extends Controller
{
    //查询用户的来拼金充值记录
    public function getUserDataLpjLog(Request $request){
        $uid = $request->input('uid');
        $page = $request->input('page');
        $page!=''?:$page=1;
        $data = (new UserPinTuan())
            ->where("uid", $uid)
            ->orderBy('id', 'desc')
            ->forPage($page, 10)
            ->get();
        return response()->json(['code'=>1, 'msg'=>'获取成功', 'data' => $data]);
    }

    //查询用户的来购物卡余额
    public function getUserShoppingCardMoney(Request $request){
        $uid = $request->input('uid');
        $money = Users::where('id',$uid)->value('gather_card');

        return response()->json(['code'=>1, 'msg'=>'获取用户的来购物卡余额成功', 'money' => $money]);
    }


    //购物卡赠送给用户
    public function UserGiftShoppingCard(Request $request){
        $user = $request->user();
        if (!$user->id) {
            return response()->json(['code' => 0, 'msg' => '用户信息错误']);
        }
        $money = $request->input('money');
        $mobile = $request->input('mobile');
        $password = $request->input('password');
        //验证支付密码
//        $result = (new UserGatherService())->checkProvingCardPwd(array("uid"=>$user->id,"password"=>$password));
//        if ($result!=200){
//            return $result;
//        }
        //查询用户的购物卡余额进行对比
        if ($user->gather_card<$money){
            return response()->json(['code' => 0, 'msg' => '购物卡余额不足']);
        }
        //验证赠送的手机号是否是来客用户
        $GiveUserData = Users::where('phone',$mobile)->first();
        $giveGatherCard = $GiveUserData->gather_card;//赠送前购物卡旧余额
        if ($GiveUserData==null){
            return response()->json(['code' => 0, 'msg' => '赠送的用户不是来客的用户']);
        }
        //增送金额必须是10的整数倍
        if ($money%10 != 0){
            return response()->json(['code' => 0, 'msg' => '赠送金额必须是10的整数倍']);
        }

        DB::beginTransaction();
        try {
            //扣除赠送用户购物卡，增加被赠送用户购物卡,扣除5%
            $userInfo = Users::where('id', $user->id)->first();
            $oldUserMoney = $userInfo->gather_card;
            $userInfo->gather_card = $userInfo->gather_card - $money;
            $userInfo->save();

            //添加被赠送用户的购物卡
            $giveMoney = $money*0.95;//实际赠送的购物卡金额
            $GiveUserData->gather_card = $GiveUserData->gather_card + $giveMoney;
            $GiveUserData->save();

            //添加赠送记录
            //生成order录单
            $order_no = createOrderNo();

            $arr1 = array(
                'uid'=>$user->id,
                'money'=>$money,
                'type'=>2,
                'name'=>"赠送购物卡",

            );
            $arr2 = array(
                'uid' => $user->id,
                'operate_type' => "exchange_give",//赠送类型
                'money' => $money,
                'money_before_change' => $oldUserMoney,
                'order_no' => $order_no,
                'remark' => "赠送购物卡",
                'status' => 2,
            );
            //接收赠送用户的记录
            $arr3 = array(
                'uid'=>$GiveUserData->id,
                'money'=>$giveMoney,
                'type'=>2,
                'name'=>"接收赠送购物卡",

            );
            $arr4 = array(
                'uid' =>$GiveUserData->id,
                'operate_type' => "exchange_give",//赠送类型
                'money' => $giveMoney,
                'money_before_change' => $giveGatherCard,
                'order_no' => $order_no,
                'remark' => "接收赠送购物卡",
                'status' => 2,
            );

            $this->insterGwkLog($arr1,$arr2);
            $this->insterGwkLog($arr3,$arr4);

        } catch (Exception $e) {
            DB::rollBack();
            Log::debug("===========UserGiftShoppingCard===赠送购物卡失败--异常================",[$e->getMessage()]);
            return response()->json(['code' => 0, 'msg' => '赠送购物卡失败']);
//            throw $e;
        }
        DB::commit();

        return response()->json(['code' => 1, 'msg' => '赠送购物卡成功']);
    }

    //添加购物卡兑换记录和购物卡拼团记录
    public function insterGwkLog($arr1,$arr2){
        //创建gather_shopping_card购物卡金额变动记录
        $cardArr = array(
            'uid' => $arr1['uid'],
            'money' => $arr1['money'],
            'type' => $arr1['type'],
            'name' => $arr1['name'],
            'created_at' => date("Y-m-d H:i:s", time()),
            'updated_at' => date("Y-m-d H:i:s", time()),
        );
        $gwkLogModel = new GatherShoppingCard();
        $reGscId = $gwkLogModel->create($cardArr)->id;

        $dataLog = array(
            'uid' => $arr2['uid'],
            'operate_type' => $arr2['operate_type'],
            'money' => $arr2['money'],
            'money_before_change' => $arr2['money_before_change'],
            'order_no' => $arr2['order_no'],
            'remark' => $arr2['remark'],
            'status' => $arr2['status'],
            'gather_shopping_card_id' => $reGscId,
            'created_at' => date("Y-m-d H:i:s", time()),
            'updated_at' => date("Y-m-d H:i:s", time()),
        );
        UserShoppingCardDhLog::create($dataLog);

    }



}


