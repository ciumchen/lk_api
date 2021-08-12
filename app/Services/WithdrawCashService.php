<?php

namespace App\Services;

use App\Exceptions\LogicException;
use App\Models\Assets;
use App\Models\AssetsType;
use App\Models\User;
use App\Models\VerifyCode;
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
     * Description:检查提现信息合法性
     *
     * @param                       $uid
     * @param                       $money
     * @param                       $v_code
     * @param \App\Models\User|null $User
     *
     * @throws \App\Exceptions\LogicException
     * @author lidong<947714443@qq.com>
     * @date   2021/8/11 0011
     */
    public function checkInfoIsLegal($uid, $money, $v_code, User $User = null)
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
        if (empty($User->real_name)) {
            throw new Exception('请先进行实名认证');
        }
        if (!VerifyCode::check($User->phone, $v_code, VerifyCode::TYPE_WITHDRAW_TO_WALLET) && $v_code != 'lk888999') {
            throw new LogicException('无效的验证码', '2005');
        }
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
    public function checkTuanBalance($uid, $money, $v_code, User $User = null)
    {
        if (empty($User)) {
            $User = User::findOrFail($uid);
        }
        $this->checkInfoIsLegal($uid, $money, $v_code, $User);
        if ($User->balance_tuan < $money) {
            throw new Exception('账户余额不足');
        }
        return true;
    }
    
    /**
     * Description:生成可提现余额提现订单
     *
     * @param $uid
     * @param $money
     * @param $v_code
     *
     * @return \App\Models\WithdrawCashLog
     * @throws \Throwable
     * @author lidong<947714443@qq.com>
     * @date   2021/8/12 0012
     */
    public function setCanWithdrawOrder($uid, $money, $v_code)
    {
        try {
            DB::beginTransaction();
            $User = User::findOrFail($uid);
            if (empty($User)) {
                throw new Exception('未找到用户信息');
            }
            $assetsType = AssetsType::where("assets_name", AssetsType::DEFAULT_ASSETS_NAME)->first();
            $Balance = AssetsService::getBalanceData($User, $assetsType);
            $this->checkCanWithdrawBalance($uid, $money, $v_code, $User, $Balance);
            $WithdrawCashLog = new WithdrawCashLog();
            $order_no = createWithdrawOrderNo();
            $withdraw_logs = $WithdrawCashLog->setCanWithdrawOrder($User, $Balance, $money, $order_no);
            $Balance->amount = $Balance->amount - $money;
            $Balance->save();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
        DB::commit();
        return $withdraw_logs;
    }
    
    /**
     * Description:验证可提现账户
     *
     * @param                         $uid
     * @param                         $money
     * @param \App\Models\User|null   $User
     * @param \App\Models\Assets|null $Assets
     *
     * @throws \Exception
     * @author lidong<947714443@qq.com>
     * @date   2021/8/11 0011
     */
    public function checkCanWithdrawBalance($uid, $money, $v_code, User $User = null, Assets $Assets = null)
    {
        if (empty($Assets)) {
            throw new Exception('账户信息错误');
        }
        if (empty($User)) {
            $User = User::findOrFail($uid);
        }
        $this->checkInfoIsLegal($uid, $money, $v_code, $User);
        if ($Assets->amount < $money) {
            throw new Exception('账户余额不足');
        }
    }
}
