<?php

namespace App\Http\Controllers\API\Order;

use App\Exceptions\LogicException;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\bmapi\MobileRechargeService;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class MobileRechargeController extends Controller
{
    /**
     * @param  \Illuminate\Http\Request  $request
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\LogicException
     * @throws \Throwable
     */
    public function setOrder(Request $request)
    {
        $mobile = $request->input('mobile');
        $money = $request->input('money');
        $reg = '/^1[3456789]\d{9}$/';
        if (preg_match($reg, $mobile) < 1) {
            throw new LogicException('手机号格式不正确');
        }
        if (!in_array($money, [50, 100, 200])) {
            throw new LogicException('话费充值金额不在可选值范围内');
        }
        $user = $request->user();
        DB::beginTransaction();
        try {
            $MobileService = new MobileRechargeService();
            $order = $MobileService->setAllOrder($user, $mobile, $money);
        } catch (Exception $e) {
            DB::rollBack();
            throw new LogicException($e->getMessage());
        }
        DB::commit();
        return response()->json(['code' => 0, 'data' => $order, 'msg' => '订单创建成功']);
    }
    
    /**
     * @param  \Illuminate\Http\Request  $request
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\LogicException
     * @throws \Throwable
     */
    public function setDlOrder(Request $request)
    {
        $mobile = $request->input('mobile');
        $money = $request->input('money');
        $reg = '/^1[3456789]\d{9}$/';
        if (preg_match($reg, $mobile) < 1) {
            throw new LogicException('手机号格式不正确');
        }
        if (!in_array($money, [50, 100, 200])) {
            throw new LogicException('话费充值金额不在可选值范围内');
        }
        $user = $request->user();
        DB::beginTransaction();
        try {
            $MobileService = new MobileRechargeService();
            $order = $MobileService->setDlAllOrder($user, $mobile, $money);
        } catch (Exception $e) {
            DB::rollBack();
            throw new LogicException($e->getMessage());
        }
        DB::commit();
        return response()->json(['code' => 0, 'data' => $order, 'msg' => '订单创建成功']);
    }
    
    /**
     * Description:
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @throws \App\Exceptions\LogicException
     * @author lidong<947714443@qq.com>
     * @date   2021/7/5 0005
     */
    public function setManyZlOrder(Request $request)
    {
        $user = $request->user();
        $params = $request->input('params');
        try {
            if ($params) {
                $data = json_decode($params, true);
            } else {
                throw new Exception('请填写对应数据');
            }
            $MobileService = new MobileRechargeService();
            $order = $MobileService->setManyZlOrder($user, $data);
        } catch (Exception $e) {
            throw $e;
            throw new LogicException($e->getMessage());
        } catch (\Throwable $e) {
            throw new LogicException($e->getMessage());
        }
        return apiSuccess($order);
    }
    
    /**
     * Description:充值测试
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     * @author lidong<947714443@qq.com>
     * @date   2021/7/5 0005
     */
    public function rechargeTest(Request $request)
    {
        $order_id = $request->input('order_id');
        $order_no = $request->input('order_no');
        $MobileService = new MobileRechargeService();
        try {
            $MobileService->recharge($order_id, $order_no);
        } catch (Exception $e) {
            throw $e;
        }
        return response()->json();
    }
}
