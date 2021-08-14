<?php

namespace App\Http\Controllers\API\Withdraw;

use App\Exceptions\LogicException;
use App\Http\Controllers\Controller;
use App\Services\WithdrawCashService;
use Exception;
use Illuminate\Http\Request;

class WithdrawController extends Controller
{
    /**
     * Description:生成拼团金提现订单
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return string
     * @throws \App\Exceptions\LogicException
     * @author lidong<947714443@qq.com>
     * @date   2021/8/10 0010
     */
    public function setTuanOrder(Request $request)
    {
        $user = $request->user();
        $money = $request->input('money');
        $v_code = $request->input('v_code');
        try {
            $Withdraw = new WithdrawCashService();
            $order = $Withdraw->setTuanWithdrawOrder($user->id, $money, $v_code);
        } catch (Exception $e) {
            throw new LogicException($e->getMessage());
        }
        return apiSuccess($order);
    }
    
    /**
     * Description:生成可体现额度[补贴]提现订单
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return string
     * @throws \App\Exceptions\LogicException
     * @throws \Throwable
     * @author lidong<947714443@qq.com>
     * @date   2021/8/13 0013
     */
    public function setCanOrder(Request $request)
    {
        $user = $request->user();
        $money = $request->input('money');
        $v_code = $request->input('v_code');
        try {
            $Withdraw = new WithdrawCashService();
            $order = $Withdraw->setCanWithdrawOrder($user->id, $money, $v_code);
        } catch (Exception $e) {
            throw new LogicException($e->getMessage());
        }
        return apiSuccess($order);
    }
    
    /**
     * Description:查询用户提现记录
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return string
     * @throws \App\Exceptions\LogicException
     * @author lidong<947714443@qq.com>
     * @date   2021/8/14 0014
     */
    public function getMyWithdrawLogs(Request $request)
    {
        $user = $request->user();
        $page = $request->input('page');
        $limit = $request->input('limit');
        try {
            $Withdraw = new WithdrawCashService();
            $list = $Withdraw->getUserWithdrawLog($user->id, $page, $limit);
        } catch (Exception $e) {
            throw new LogicException($e->getMessage());
        }
        return apiSuccess($list);
    }
}
