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
use Bmapi\Api\MobileRecharge\PayBill;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\API\Payment\YuntongPayController;
use App\Http\Requests\UserPinTuan as ReUserPinTuan;

class UserShoppingCardController extends Controller
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



}
