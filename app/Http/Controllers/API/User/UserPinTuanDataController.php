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

        return response()->json(['code'=>1, 'msg'=>'获取成功', 'money' => $money]);
    }


}
