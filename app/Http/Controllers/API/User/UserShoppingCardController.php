<?php

namespace App\Http\Controllers\API\User;

use App\Exceptions\LogicException;
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

class UserShoppingCardController extends Controller
{
    //查询购物卡兑换记录
    public function selectGwkDhjl(Request $request){
        $uid = $request->input('uid');
        $page = $request->input("page");
        $data = (new GatherShoppingCard())
            ->whith(['gwkDhLog'])
            ->where(["uid"=>$uid,"gwkDhLog.status"=>2])
            ->orderBy('id', 'desc')
            ->latest('id')
            ->forPage($page, 10)
            ->get(['id','uid','money','status','type','name','created_at']);

        return response()->json(['code'=>1, 'msg'=>'获取成功', 'data' => $data]);

    }



}
