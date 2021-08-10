<?php

namespace App\Http\Controllers\API\Withdraw;

use App\Exceptions\LogicException;
use App\Http\Controllers\Controller;
use App\Services\WithdrawCashService;
use Illuminate\Http\Request;

class WithdrawController extends Controller
{
    //
    public function setWithDrawOrder(Request $request)
    {
        $user = $request->user();
        $money = $request->input('money');
        try {
            setTuanWithdrawOrder();
        } catch (\Exception $e) {
            throw new LogicException($e->getMessage());
        }
    }
    
    //生成提现订单
    
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
        try {
            $Withdraw = new WithdrawCashService();
            $order = $Withdraw->setTuanWithdrawOrder($user->id, $money);
        } catch (\Exception $e) {
            throw new LogicException($e->getMessage());
        }
        return apiSuccess($order);
    }
    
    
}
