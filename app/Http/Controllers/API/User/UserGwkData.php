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

class UserGwkData extends Controller
{
    //查询购物卡兑换记录
    public function selectGwkDhjl2(Request $request){
        $uid = $request->input('uid');
        $page = $request->input("page");
        $data = (new UserShoppingCardDhLog())
//            ->with(['GatherShoppingCard'=>function($query) use ($uid){
//                $query->where(["uid"=>$uid]);
//            }])
            ->with(['GatherShoppingCard'])
            ->where('status',2)
            ->where(["uid"=>$uid])
            ->orderBy('id', 'desc')
            ->latest('id')
            ->forPage($page, 10)
            ->get();

        return response()->json(['code'=>1, 'msg'=>'获取成功', 'data' => $data]);

    }

    //查询购物卡兑换记录
    public function selectGwkDhjl_bak(Request $request){
        $uid = $request->input('uid');
        $page = $request->input("page");
        $data = (new GatherShoppingCard())
            ->with(['gwkDhLog'=>function($query) use ($uid){
                $query->where('status',2);
            }])
//            ->with(['gwkDhLog'])

            ->where(["uid"=>$uid])
            ->orderBy('id', 'desc')
            ->latest('id')
            ->forPage($page, 10)
            ->get();

        return response()->json(['code'=>1, 'msg'=>'获取成功', 'data' => $data]);

    }

    //查询购物卡拼团中奖记录
    public function selectGwkPingtuanLog(Request $request){
        $uid = $request->input('uid');
        $page = $request->input("page");
        $data = (new GatherShoppingCard())->where(["uid"=>$uid,'gid'=>0,'guid'=>0])
            ->orderBy('id', 'desc')
            ->latest('id')
            ->forPage($page, 10)
            ->get();
        return response()->json(['code'=>1, 'msg'=>'获取成功', 'data' => $data]);

    }

    //查询购物卡记录
    public function selectGwkXfLog(Request $request){
        $uid = $request->input('uid');
        $page = $request->input("page");
        $data = (new UserShoppingCardDhLog())->where(["uid"=>$uid,'status'=>2])
            ->wh
            ->orderBy('id', 'desc')
            ->latest('id')
            ->forPage($page, 10)
            ->get();
        return response()->json(['code'=>1, 'msg'=>'获取成功', 'data' => $data]);

    }


}
