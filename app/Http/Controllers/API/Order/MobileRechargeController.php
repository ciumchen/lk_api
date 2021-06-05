<?php

namespace App\Http\Controllers\api\Order;

use App\Exceptions\LogicException;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\bmapi\MobileRechargeService;
use DB;
use Illuminate\Http\Request;

class MobileRechargeController extends Controller
{
    
    public function setOrder(Request $request)
    {
        $mobile = $request->input('mobile');
        $money = $request->input('money');
        $reg = '/^1[3456789]\d{9}$/';
        if (preg_match($reg, $mobile) < 1) {
            throw new LogicException('手机号格式不正确');
        }
//        if (!in_array($money, [50, 100, 200])) {
//            throw new LogicException('话费充值金额不在可选值范围内');
//        }
        $user = $request->user();
        DB::beginTransaction();
        try {
            $MobileService = new MobileRechargeService();
            $order = $MobileService->setAllOrder($user, $mobile, $money);
        } catch (\Exception $e) {
            DB::rollBack();
            throw new LogicException($e->getMessage());
        }
        DB::commit();
        return response()->json(['code' => 0, 'data' => $order, 'msg' => '订单创建成功']);
    }
    
    public function rechargeTest(Request $request)
    {
        $order_id = $request->input('order_id');
        $order_no = $request->input('order_no');
        $MobileService = new MobileRechargeService();
        $MobileService->recharge($order_id, $order_no);
    }
}
