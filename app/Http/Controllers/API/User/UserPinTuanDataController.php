<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use App\Libs\Yuntong\YuntongPay;
use App\Models\Assets;
use App\Models\AssetsLogs;
use App\Models\GatherShoppingCard;
use App\Models\Order;
use App\Models\UserPinTuan;
use App\Models\Users;
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
        $result = (new UserGatherService())->checkProvingCardPwd(array("uid"=>$user->id,"password"=>$password));
        if ($result!=200){
            return $result;
        }
        //查询用户的购物卡余额进行对比
        if ($user->gather_card<$money){
            return response()->json(['code' => 0, 'msg' => '购物卡余额不足']);
        }
        //验证赠送的手机号是否是来客用户
        $GiveUserData = Users::where('phone',$mobile)->first();
        if ($GiveUserData==null){
            return response()->json(['code' => 0, 'msg' => '赠送的用户不是来客的用户']);
        }

        //扣除赠送用户购物卡，增加被赠送用户购物卡

        //添加两个用户购物卡变动记录


    }
}
