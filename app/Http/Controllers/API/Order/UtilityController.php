<?php

namespace App\Http\Controllers\API\Order;

use App\Exceptions\LogicException;
use App\Http\Controllers\Controller;
use App\Services\bmapi\UtilityBillRechargeService;
use Exception;
use Illuminate\Http\Request;

class UtilityController extends Controller
{
    
    /**
     * Description:查询可充值项
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return string
     * @throws \App\Exceptions\LogicException
     * @author lidong<947714443@qq.com>
     * @date   2021/6/15 0015
     */
    public function getItemList(Request $request)
    {
        $project_id = $request->input('project_id');
        $city = $request->input('city');
        try {
            $UtilityService = new UtilityBillRechargeService();
            $list = $UtilityService->searchList($project_id, $city);
            if (empty($list)) {
                throw new Exception('该城市暂不支持该服务');
            }
        } catch (Exception $e) {
            throw new LogicException($e->getMessage());
        }
        return apiSuccess($list);
    }
    
    /**
     * Description:账单查询
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return string
     * @throws \App\Exceptions\LogicException
     * @author lidong<947714443@qq.com>
     * @date   2021/6/15 0015
     */
    public function checkBill(Request $request)
    {
        $project_id = $request->input('project_id');
        $item_id = $request->input('item_id');
        $account = $request->input('account');
        try {
            $UtilityService = new UtilityBillRechargeService();
            $bill = $UtilityService->checkBill($item_id, $account, $project_id);
        } catch (Exception $e) {
            throw new LogicException($e->getMessage());
        }
        return apiSuccess($bill);
    }
    
    /**
     * Description:生成缴费订单
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return string
     * @throws \App\Exceptions\LogicException
     * @throws \Throwable
     * @author lidong<947714443@qq.com>
     * @date   2021/6/15 0015
     */
    public function setOrder(Request $request)
    {
        $user = $request->user();
        $account = $request->input('account');
        $project_id = $request->input('project_id');
        $item_id = $request->input('item_id');
        $money = $request->input('money');
        $bill_cycle = $request->input('bill_cycle');
        $contract_no = $request->input('contract_no');
        $contract_id = $request->input('contract_id');
        $item4 = $request->input('item4');
        try {
            $UtilityService = new UtilityBillRechargeService();
            $order = $UtilityService->setUtilityOrder(
                $user,
                $account,
                $project_id,
                $item_id,
                $money,
                $bill_cycle,
                $contract_no,
                $contract_id,
                $item4
            );
        } catch (Exception $e) {
            throw new LogicException($e->getMessage());
        }
        return apiSuccess($order);
    }
}
