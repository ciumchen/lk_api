<?php

namespace App\Services;

use App\Exceptions\LogicException;
use App\Models\Address;
use App\Models\AssetsLogs;
use App\Models\AssetsType;
use App\Models\BanList;
use App\Models\User;
use App\Models\WithdrawLogs;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Exception;
use App\Models\Setting;
class IetsWithdrawalService
{
    /**
     * 提现
     * @param User $user
     * @param $amount
     * @param $address
     * @param array $options
     * @return bool
     * @throws LogicException
     * @throws \EthereumRPC\Exception\ConnectionException
     * @throws \EthereumRPC\Exception\ContractABIException
     * @throws \EthereumRPC\Exception\ContractsException
     * @throws \EthereumRPC\Exception\GethException
     * @throws \HttpClient\Exception\HttpClientException
     */
    public function transfer(User $user, $amount, $address, $options = [])
    {
        if (strlen($address) != 42) {
            throw new LogicException('请输入正确的钱包地址');
        }

        //检测托管地址余额
//        if (false === (new AssetsService())->checkPayerBalance($amount)) {
//            throw new LogicException('暂停提现，请等待');
//        }
//dd(112222);
        if (bccomp($amount, 0, 8) <= 0) {
            throw new LogicException('提现数量异常');
        }

//        if (bccomp($amount, 2, 8) < 0) {
//            throw new LogicException('最低提现2个');
//        }

//        if (bccomp($amount, 100, 8) > 0) {
//            throw new LogicException('单笔最多提现100');
//        }

//        //一小时只能划转1次
//        $lastWithdrawLog = WithdrawLogs::where('uid', $user->id)->where('assets_type', AssetsType::DEFAULT_ASSETS_NAME)->latest('id')->first();
//        if ($lastWithdrawLog && now()->subHours(1)->lt($lastWithdrawLog->created_at)) {
//            throw new LogicException('提现间隔时间不低于 1 小时');
//        }

//        $todayWithdrawAmount = WithdrawLogs::where('created_at', '>=', Carbon::now()->startOfDay()->toDateTimeString())
//            ->where('status', WithdrawLogs::STATUS_DONE)
//            ->where('uid', $user->id)
//            ->sum(DB::raw('amount+fee'));
//        if (bccomp(bcadd($amount, $todayWithdrawAmount ?? 0, 8), 500, 8) > 0) {
//            throw new LogicException('每天最多提现500');
//        }

        $asset = AssetsType::where('assets_name', 'iets')->first();//资产类型
        $userBalance = AssetsService::getBalanceData($user, $asset);//iets数量
//dd($userBalance);
        if (0 !== bccomp($userBalance->amount, AssetsLogs::where('assets_type_id', $asset->id)->where('uid', $user->id)->sum('amount'), 8)) {
            $banlist = BanList::updateOrCreate([
                'uid' => $user->id,
                'reason' => '余额记录异常',
                'ip' => $options['ip'] ?? '',
            ]);

            $user->update([
                'status' => 2,
                'ban_reason' => $banlist->reason,
            ]);

            throw new LogicException('账户已禁用，原因：'.$banlist->reason);
        }


        DB::beginTransaction();
        try {





            $fromBalance = AssetsService::getBalanceData($user, $asset, true);//iets数量

            if (null === $fromBalance || bccomp($fromBalance->amount, $amount, 8) < 0) {
                throw new LogicException('余额不足');
            }

            $fee = $this->calculateFee($amount, $asset);

            $toAmount = bcsub($amount, $fee, 8); //接收数量
//dd($fee);
//dd($toAmount);
            $fromBalance->change(
                -$amount,
                AssetsLogs::OPERATE_TYPE_WITHDRAW_TO_WALLET,
                'iets提现到钱包',
                $options
            );
            if ($fee > 0) {
                $feeUser = User::find(2);
                $feeBalance = AssetsService::getBalanceData($feeUser, $asset, true);
                $feeBalance->change(
                    $fee,
                    AssetsLogs::OPERATE_TYPE_WITHDRAW_TO_WALLET_FEE,
                    '收取iets提现手续费',
                    $options
                );
            }

            //添加提现记录
            $withdrawLog = new WithdrawLogs();
            $withdrawLog->uid = $user->id;
            $withdrawLog->assets_type_id = $asset->id;
            $withdrawLog->assets_type = 'iets';
            $withdrawLog->status = 2; //2为提现成功
            $withdrawLog->amount = $toAmount;
            $withdrawLog->fee = $fee;
            $withdrawLog->address = $address;
            $withdrawLog->ip = $options['ip'] ?? '';
            $withdrawLog->remark = 'iets提现到钱包';
            $withdrawLog->user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? mb_substr($_SERVER['HTTP_USER_AGENT'], 0, 255, 'utf-8') : '';

            if (bccomp($asset->large_withdraw_amount, 0, 8) && bccomp($amount, $asset->large_withdraw_amount, 8) >= 0) {
                $withdrawLog->status = 3; //3为待审核
            }

            $withdrawLog->save();




            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            report($e);

            if ($e instanceof LogicException) {
                throw $e;
            }

            throw new LogicException('提现失败，请稍后再试');
        }

//        if (3 != $withdrawLog->status) {
//            try {
//                $address = $address;
//                $txHash = (new AssetsService())->transfer($toAmount, $address);
//                if (!$txHash) {
//                    throw new LogicException('提现失败', 108);
//                }
//                $withdrawLog->tx_hash = $txHash;
//                $withdrawLog->save();
//            } catch (Exception $e) {
//                report($e);
//
//                if ($e instanceof LogicException) {
//                    throw $e;
//                }
//                throw new LogicException('提现失败，请联系客服');
//            }
//        }

        return true;
    }

    /**
     * 计算提现手续费
     * @param $amount
     * @param $assetsType
     * @return int|string
     */
    public function calculateFee($amount, $assetsType)
    {
        if($assetsType->assets_name='iets'){
            $fee = 0;
            $setFee = Setting::where('key','free_service_charge')->value('value');
            $fee = bcdiv(bcmul($amount, $setFee,8), 100, 8);
            return $fee;
        }else{
            return false;
        }

    }
}
