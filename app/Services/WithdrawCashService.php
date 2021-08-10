<?php

namespace App\Services;

use App\Models\User;
use App\Models\WithdrawCashLog;
use Exception;
use Illuminate\Support\Facades\DB;

class WithdrawCashService
{
    /**
     * Description:生成拼团金提现订单
     *
     * @param $uid
     * @param $money
     *
     * @return \App\Models\WithdrawCashLog
     * @throws \Throwable
     * @author lidong<947714443@qq.com>
     * @date   2021/8/10 0010
     */
    public function setTuanWithdrawOrder($uid, $money)
    {
        try {
            DB::beginTransaction();
            $User = User::findOrFail($uid);
            if (empty($User)) {
                throw new Exception('未找到用户信息');
            }
            $this->checkTuanBalance($uid, $money, $User);
            $WithdrawCashLog = new WithdrawCashLog();
            $order_no = createWithdrawOrderNo();
            $withdraw_logs = $WithdrawCashLog->setPinTuanOrder($User, $money, $order_no);
            $User->balance_tuan = $User->balance_tuan - $money;
            $User->save();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
        DB::commit();
        return $withdraw_logs;
    }
    
    /**
     * Description:提现数据验证
     *
     * @param                       $uid
     * @param                       $money
     * @param \App\Models\User|null $User
     *
     * @return bool
     * @throws \Exception
     * @author lidong<947714443@qq.com>
     * @date   2021/8/10 0010
     */
    public function checkTuanBalance($uid, $money, User $User = null)
    {
        if (empty($User)) {
            $User = User::findOrFail($uid);
        }
        if ($money < 100) {
            throw new Exception('提现金额不能小于100');
        }
        if ($money % 100) {
            throw new Exception('提现金额只能是100的倍数');
        }
        if ($User->balance_tuan < $money) {
            throw new Exception('账户余额不足');
        }
        return true;
    }
    //TODO:生成可提现余额提现订单
}
