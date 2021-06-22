<?php

namespace App\Services;

use App\Exceptions\LogicException;
use App\Models\Address;
use App\Models\Assets;
use App\Models\AssetsLogs;
use App\Models\AssetsType;
use App\Models\BanList;
use App\Models\Setting;
use App\Models\User;
use App\Models\WithdrawLogs;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Log;
class GiveIetsService
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
    public function transfer(User $user, $amount, $phone, $options = [])
    {

        if (bccomp($amount, 0, 8) <= 0) {
            throw new LogicException('赠送数量异常','1001');
        }

        if (bccomp($amount, 2, 8) < 0) {
            throw new LogicException('最低赠送2个','1002');
        }

//        if (bccomp($amount, 100, 8) > 0) {
//            throw new LogicException('单笔最多赠送100','1003');
//        }

        //一小时只能划转1次
//        $lastWithdrawLog = WithdrawLogs::where('uid', $user->id)->where('assets_type', AssetsType::DEFAULT_ASSETS_NAME)->latest('id')->first();
//        if ($lastWithdrawLog && now()->subHours(1)->lt($lastWithdrawLog->created_at)) {
//            throw new LogicException('赠送间隔时间不低于 1 小时');
//        }

//        $todayWithdrawAmount = WithdrawLogs::where('created_at', '>=', Carbon::now()->startOfDay()->toDateTimeString())
//            ->where('status', WithdrawLogs::STATUS_DONE)
//            ->where('uid', $user->id)
//            ->sum(DB::raw('amount+fee'));
//        if (bccomp(bcadd($amount, $todayWithdrawAmount ?? 0, 8), 1000, 8) > 0) {
//            throw new LogicException('每天最多赠送1000','1004');
//        }

        $asset = AssetsType::where('assets_name','iets')->first();
        $userBalance = AssetsService::getBalanceData($user, $asset);//获取资产
//dd($userBalance);
        //比较两个数相等返回0，不相等返回1或-1

//        $re1 = $userBalance->amount;
//        $re2 = AssetsLogs::where('assets_type_id', $asset->id)->where('uid', $user->id)->sum('amount');
//dd($re1,$re2);

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

            throw new LogicException('账户已禁用，原因：'.$banlist->reason,'1005');
        }


        //开启事物
//        dd($userBalance);
        DB::beginTransaction();
        try {



            $fromBalance = AssetsService::getBalanceData($user, $asset, true);//获取用户资产
//dd($fromBalance);
//            Log::info("打印赠送日志===0000==========".$fromBalance);
            if (null === $fromBalance || bccomp($fromBalance->amount, $amount, 8) < 0) {
                throw new LogicException('余额不足','1006');
            }
//            dd($amount);
//            dd($asset);
            $fee = $this->calculateFee($amount, $asset);//扣手续费数量
//dd($fee);
            $toAmount = bcsub($amount, $fee, 8); //接收数量

            $fromBalance->change(//变更余额
                -$amount,
                AssetsLogs::OPERATE_TYPE_MARKET_BUSINESS,
                '赠送给市商',
                $options
            );
            //赠送给市商
            $userInfo = User::where('phone',$phone)->first();
            $giveAssetInfo = AssetsService::getBalanceData($userInfo, $asset, true);
//            dd($userInfo);
            $giveAssetInfo->change(//变更余额
                +$toAmount,
                AssetsLogs::OPERATE_TYPE_MARKET_BUSINESS,
                '获得用户赠送',
                $options
            );

            if ($fee > 0) {
                $feeUser = User::find(2);
                $feeBalance = AssetsService::getBalanceData($feeUser, $asset, true);
                $feeBalance->change(
                    $fee,
                    AssetsLogs::OPERATE_TYPE_MARKET_BUSINESS,
                    '收取赠送手续费',
                    $options
                );
            }
//            //添加赠送记录
//            $withdrawLog = new WithdrawLogs();
//            $withdrawLog->uid = $user->id;
//            $withdrawLog->assets_type_id = $asset->id;
//            $withdrawLog->assets_type = 'iets';
//            $withdrawLog->status = 2; //2为提现成功
//            $withdrawLog->amount = $toAmount;
//            $withdrawLog->fee = $fee;
//            $withdrawLog->address = $phone;
//            $withdrawLog->ip = $options['ip'] ?? '';
//            $withdrawLog->remark = '赠送给市商';
//            $withdrawLog->user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? mb_substr($_SERVER['HTTP_USER_AGENT'], 0, 255, 'utf-8') : '';
//            if (bccomp($asset->large_withdraw_amount, 0, 8) && bccomp($amount, $asset->large_withdraw_amount, 8) >= 0) {
//                $withdrawLog->status = 3; //3为待审核
//            }
//            $withdrawLog->save();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            report($e);
            if ($e instanceof LogicException) {
                throw $e;
            }
            throw new LogicException('赠送失败，请稍后再试','1007');
        }

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
        if($assetsType->assets_name='usdt'){
            $fee = 0;
            $setFee = Setting::where('key','free_service_charge')->value('value');
            $fee = bcdiv(bcmul($amount, $setFee,8), 100, 8);
            return $fee;
        }else{
            return false;
        }

    }
}
