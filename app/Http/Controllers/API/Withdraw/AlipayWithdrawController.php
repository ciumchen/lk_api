<?php

namespace App\Http\Controllers\API\Withdraw;

use App\Exceptions\LogicException;
use App\Http\Controllers\Controller;
use App\Models\WithdrawCashLog;
use App\Services\Alipay\AlipayCertService;
use App\Services\WithdrawCashService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AlipayWithdrawController extends Controller
{
    /**
     * Description:发起转账请求
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return string
     * @throws \App\Exceptions\LogicException
     * @author lidong<947714443@qq.com>
     * @date   2021/8/11 0011
     */
    public function payUser(Request $request)
    {
        $withdraw_id = $request->input('withdraw_id');
        try {
            $Withdraw = WithdrawCashLog::findOrFail($withdraw_id);
            if ($Withdraw->status != WithdrawCashLog::STATUS_DEFAULT) {
                throw new LogicException('该请求不在待处理状态');
            }
            $AlipayService = new AlipayCertService();
            $res = $AlipayService->payToUser(
                $Withdraw->alipay_user_id,
                $Withdraw->real_name,
                $Withdraw->order_no,
                $Withdraw->actual_amount,
                $Withdraw->remark
            );
            $Withdraw->pay_fund_order_id = $res->pay_fund_order_id;
            $Withdraw->out_trade_no = $res->order_id;
            $Withdraw->alipay_status = $res->status;
            $Withdraw->trans_date = $res->trans_date;
            $Withdraw->status = WithdrawCashLog::STATUS_SUCCESS;
            $Withdraw->channel = 'alipay';
            $Withdraw->save();
        } catch (Exception $e) {
            Log::debug('提现失败:AlipayWithdraw', [$e->getMessage()]);
            $failed = json_decode($e->getMessage());
            if (isset($failed->sub_msg)) {
                $Withdraw = WithdrawCashLog::findOrFail($withdraw_id);
                $Withdraw->failed_reason = $failed->sub_msg;
                $Withdraw->status = WithdrawCashLog::STATUS_FAILED;
                $Withdraw->save();
            }
            throw new LogicException('提现失败');
        }
        return apiSuccess([], '提现成功');
    }
}
