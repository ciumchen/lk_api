<?php

namespace App\Services;

use App\Exceptions\LogicException;


use App\Models\Asset;
use App\Models\Assets;
use App\Models\AssetsLogs;
use App\Models\AssetsType;
use App\Models\User;
use ERC20\ERC20;
use quarkblockchain\QkNodeRPC;

class AssetsService
{
    /**获取资产
     * @param User $user
     * @param AssetsType $assetType
     * @param bool $isLock
     * @return Assets
     */
    public static function getBalanceData(User $user, AssetsType $assetType, bool $isLock = false)
    {
        $assets = Assets::where('uid', $user->id)
            ->where('assets_type_id', $assetType->id)
            ->when($isLock, function ($query) {
                return $query->lockForUpdate();
            })
            ->first();
        if (null === $assets) {
            $assets = new Assets();
            $assets->uid = $user->id;
            $assets->assets_type_id = $assetType->id;
            $assets->assets_name = $assetType->assets_name;
            $assets->amount = 0;
            $assets->freeze_amount = 0;
            $assets->save();
        }

        return $assets;
    }

    /**
     * @param User $user
     * @param AssetsType $assetType
     * @param $amount
     * @return array
     * @throws LogicException
     */
    public static function changeWithoutLog(User $user, AssetsType $assetType, $amount)
    {
        //当前余额
        $assets = self::getBalanceData($user, $assetType, true);
        $beforeAmount = $assets->getRawOriginal('amount');

        $afterAmount = bcadd($beforeAmount, $amount, 18);
        if (bccomp($afterAmount, 0, 18) < 0) {
            throw new LogicException('余额不足');
        }

        $assets->amount = $afterAmount;
        $assets->save();

        return ['amount_before_change' => $beforeAmount, 'amount_after_change' => $afterAmount];
    }

    /**资产变动
     * @param User $user
     * @param AssetsType $assetType
     * @param $amount
     * @param string $operateType
     * @param array $options
     * @return mixed
     * @throws LogicException
     */
    public static function balancesChange(User $user, AssetsType $assetType, $amount, string $operateType, array $options)
    {
        $info = self::changeWithoutLog($user, $assetType, $amount);

        //写入日志
        $assetsLogs = new AssetsLogs();
        $assetsLogs->uid = $user->id;
        $assetsLogs->assets_type_id = $assetType->id;
        $assetsLogs->assets_name = $assetType->assets_name;
        $assetsLogs->amount = $amount;
        $assetsLogs->operate_type = $operateType;
        $assetsLogs->amount_before_change = $info['amount_before_change'];
        $assetsLogs->tx_id = $options['transaction_id'] ?? 0;
        $assetsLogs->tx_hash = $options['tx_hash'] ?? null;
        $assetsLogs->ip = $options['ip'] ?? '';
        $assetsLogs->user_agent = $options['user_agent'] ?? '';
        $assetsLogs->remark = $options['remark'] ?? '';
        $assetsLogs->save();

        return $assetsLogs->id;
    }


    /**
     * 检查托管地址余额.
     * @param $num
     * @return bool
     * @throws \EthereumRPC\Exception\ConnectionException
     * @throws \EthereumRPC\Exception\ContractABIException
     * @throws \EthereumRPC\Exception\ContractsException
     * @throws \EthereumRPC\Exception\GethException
     * @throws \HttpClient\Exception\HttpClientException
     */
    public function checkPayerBalance($num)
    {
        //合约地址
        $asset = AssetsType::where('assets_name', AssetsType::DEFAULT_ASSETS_NAME)->first();
        $contract = $asset->contract_address;
        $urlArr = parse_url(env('WITHDRAW_RPC_HOST'));

        //实例化节点对象
        $qkNode = new QkNodeRPC($urlArr['host'], $urlArr['port']);
        $erc = new ERC20($qkNode);
        $token = $erc->token($contract);
        //托管地址（发送方）
        $payer = env('WITHDRAW_ADDRESS');
        $payerBalance = $token->balanceOf($payer);

        if (bccomp($payerBalance, bcadd($num, 1000, 8), 0) < 0) {
            return false;
        }

        return true;
    }

    /**
     * 转出.
     *
     * @param $amount
     * @param $address
     *
     * @return false|string
     *
     * @throws \ERC20\Exception\ERC20Exception
     * @throws \EthereumRPC\Exception\ConnectionException
     * @throws \EthereumRPC\Exception\ContractABIException
     * @throws \EthereumRPC\Exception\ContractsException
     * @throws \EthereumRPC\Exception\GethException
     * @throws \EthereumRPC\Exception\RawTransactionException
     * @throws \HttpClient\Exception\HttpClientException
     */
    public function transfer($amount, $address)
    {
        //合约地址
        $asset = AssetsType::where('assets_name', AssetsType::DEFAULT_ASSETS_NAME)->first();
        $contract = $asset->contract_address;
        $urlArr = parse_url(env('WITHDRAW_RPC_HOST'));

        //实例化节点对象
        $qkNode = new QkNodeRPC($urlArr['host'], $urlArr['port']);
        $erc = new ERC20($qkNode);
        $token = $erc->token($contract);
        //托管地址（发送方）
        $payer = env('WITHDRAW_ADDRESS');
        //转账
        $data = $token->encodedTransferData($address, $amount);
        $transaction = $qkNode->personal()->transaction($payer, $contract)
            ->amount('0')
            ->data($data);
        $transaction->gas(90000, '0.0000001');
        $txId = $transaction->send(env('WITHDRAW_PASSWORD'));

        if ($txId && 66 == strlen($txId)) {
            //返回交易hashco
            return $txId;
        }

        return false;
    }
}
