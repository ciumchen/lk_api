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
class AssetConversionService
{
    /**
     * 转换
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
    public function transfer($user, $amount, $transformation,$converted,$options = [])
    {

        if (bccomp($amount, 0, 8) <= 0) {
            throw new LogicException('转换数量异常','1001');
        }

        if (bccomp($amount, 1, 8) < 0) {
            throw new LogicException('最低转换1个','1002');
        }

//        if (bccomp($amount, 100, 8) > 0) {
//            throw new LogicException('单笔最多赠送100','1003');
//        }

        //一小时只能划转1次
        $lastWithdrawLog = WithdrawLogs::where('uid', $user->id)->where('assets_type', AssetsType::ASSETS_NAME_USDT_TO_IETS)->latest('id')->first();
        if ($lastWithdrawLog && now()->subHours(1)->lt($lastWithdrawLog->created_at)) {
            throw new LogicException('一小时之内只能转换一次');
        }

//        $todayWithdrawAmount = WithdrawLogs::where('created_at', '>=', Carbon::now()->startOfDay()->toDateTimeString())
//            ->where('status', WithdrawLogs::STATUS_DONE)
//            ->where('uid', $user->id)
//            ->sum(DB::raw('amount+fee'));
//        if (bccomp(bcadd($amount, $todayWithdrawAmount ?? 0, 8), 1000, 8) > 0) {
//            throw new LogicException('每天最多赠送1000','1004');
//        }

        $iets_asset = AssetsType::where('assets_name', 'iets')->first();
        $usdt_asset = AssetsType::where('assets_name', AssetsType::DEFAULT_ASSETS_NAME)->first();

//        dd($usdt_asset);
        $userBalance_iets = AssetsService::getBalanceData($user, $iets_asset);//获取资产
        $userBalance_usdt = AssetsService::getBalanceData($user, $usdt_asset);//获取资产

//dd($userBalance_usdt);
        //比较两个数相等返回0，不相等返回1或-1

//        $re1 = $userBalance_iets->amount;
//        $re2 = AssetsLogs::where('assets_type_id', $iets_asset->id)->where('uid', $user->id)->sum('amount');
//dd($re1,$re2);

        if (0 !== bccomp($userBalance_iets->amount, AssetsLogs::where('assets_type_id', $iets_asset->id)->where('uid', $user->id)->sum('amount'), 8)) {
            $banlist = BanList::updateOrCreate([
                'uid' => $user->id,
                'reason' => 'iets余额记录异常',
                'ip' => $options['ip'] ?? '',
            ]);

            $user->update([
                'status' => 2,
                'ban_reason' => $banlist->reason,
            ]);

            throw new LogicException('账户已禁用，原因：'.$banlist->reason,'1005');
        }

//        $re1 = $userBalance_usdt->amount;//错
//        $re2 = AssetsLogs::where('assets_type_id', $usdt_asset->id)->where('uid', $user->id)->sum('amount');//对
//        dd($re1,$re2);

        if (0 !== bccomp($userBalance_usdt->amount, AssetsLogs::where('assets_type_id', $usdt_asset->id)->where('uid', $user->id)->sum('amount'), 8)) {
            $banlist = BanList::updateOrCreate([
                'uid' => $user->id,
                'reason' => 'usdt余额记录异常',
                'ip' => $options['ip'] ?? '',
            ]);

            $user->update([
                'status' => 2,
                'ban_reason' => $banlist->reason,
            ]);

            throw new LogicException('账户已禁用，原因：'.$banlist->reason,'1005');
        }

        //开启事物
        DB::beginTransaction();
        try {
        //获取兑换比例
        $blData = Setting::where('key','usdt_iets_subscription_ratio')->value('value');
        if ($blData != '' && strstr($blData,'|') != false) {
            $bldateArr = explode('|', $blData);
            $blArr['usdtBl'] = $bldateArr[0];
            $blArr['ietsBl'] = $bldateArr[1];
        }else{
            throw new LogicException('usdt兑换iets的比例参数错误');
        }

        $assetData = array('transformation'=>'','converted'=>'','amount_before_change'=>'');
        if($transformation=='usdt'){
            $fromBalance = $userBalance_usdt;//a兑换类型数量
            $toBalance = $userBalance_iets;//b被兑换类型数量
            $assetData['transformation']='usdt';
            $assetData['converted']='iets';
            $assetData['amount_before_change'] = $fromBalance->amount;//a兑换的变动前数量
            $assetData['to_amount_before_change'] = $toBalance->amount;//b被兑换类型资产的变动前数量
            $assetData['converted_to_amount_before_change'] = bcdiv(($amount*$blArr['ietsBl']),$blArr['usdtBl'],8);//b被兑换类型资产实际转换数量
        }elseif ($transformation=='iets'){
            $fromBalance = $userBalance_iets;
            $toBalance = $userBalance_usdt;
            $assetData['transformation']='iets';
            $assetData['converted']='usdt';
            $assetData['amount_before_change'] = $fromBalance->amount;//a兑换的变动前数量
            $assetData['to_amount_before_change'] = $toBalance->amount;//b被兑换类型资产的变动前数量
            $assetData['converted_to_amount_before_change'] = bcdiv(($amount*$blArr['usdtBl']),$blArr['ietsBl'],8);//b被兑换类型资产实际转换数量
        }
//dd($assetData);
            if (null === $fromBalance || bccomp($fromBalance->amount, $amount, 8) < 0) {
                throw new LogicException($assetData['transformation'].'转换数量不能大于余额','1006');
            }


            //兑换的变更余额
            $fromBalance->usdt_to_iets_change(
                $user->id,
                $assetData['transformation'],
                -$amount,
                $assetData['amount_before_change'],
                AssetsLogs::OPERATE_TYPE_USDT_TO_IETS,
                $transformation.'转换'.$converted,
                $options
            );

            //转换
            $toBalance->usdt_to_iets_change(
                $user->id,
                $assetData['converted'],
                +$assetData['converted_to_amount_before_change'],
                $assetData['to_amount_before_change'],
                AssetsLogs::OPERATE_TYPE_USDT_TO_IETS,
                $transformation.'转换'.$converted,
                $options
            );


//            //添加赠送记录
            $withdrawLog = new WithdrawLogs();
            $withdrawLog->uid = $user->id;
            $withdrawLog->assets_type_id = 0;
            $withdrawLog->assets_type = AssetsType::ASSETS_NAME_USDT_TO_IETS;
            $withdrawLog->status = 2; //2为提现成功
            $withdrawLog->amount = $amount;
            $withdrawLog->fee = 0.00000000;
            $withdrawLog->address = '0x00000000000000000';
            $withdrawLog->ip = $options['ip'] ?? '';
            $withdrawLog->remark = 'usdt转换iets';
            $withdrawLog->user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? mb_substr($_SERVER['HTTP_USER_AGENT'], 0, 255, 'utf-8') : '';
            $withdrawLog->status = 2;
//            if (bccomp($asset->large_withdraw_amount, 0, 8) && bccomp($amount, $asset->large_withdraw_amount, 8) >= 0) {
//                $withdrawLog->status = 3; //3为待审核
//            }
            $withdrawLog->save();


            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            report($e);
            if ($e instanceof LogicException) {
                throw $e;
            }
            throw new LogicException('转换失败，请稍后再试','1007');
        }

        return true;
    }

}
