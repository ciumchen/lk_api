<?php

namespace App\Services;

use App\Exceptions\LogicException;
use App\Models\Assets;
use App\Models\AssetsLogs;
use App\Models\AssetsType;
use App\Models\User;
use ERC20\ERC20;
use Illuminate\Support\Facades\DB;
use phpDocumentor\Reflection\Types\False_;
use quarkblockchain\QkNodeRPC;

class AssetsService
{
    /**获取资产
     *
     * @param User       $user
     * @param AssetsType $assetType
     * @param bool       $isLock
     *
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
    
    public static function give_iets_getBalanceData(User $user, AssetsType $assetType, bool $isLock = false)
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
            $assets->assets_type_id = 2;
            $assets->assets_name = 'iets';
            $assets->amount = 0;
            $assets->freeze_amount = 0;
            $assets->save();
        }
        return $assets;
    }
    
    /**
     * @param int        $uid
     * @param AssetsType $assetType
     * @param            $amount
     *
     * @return array
     * @throws LogicException
     */
    public static function changeWithoutLog(int $uid, AssetsType $assetType, $amount)
    {
        $user = User::find($uid);
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
    
    /**
     * @param int        $uid
     * @param AssetsType $assetType
     * @param string     $assets_name
     * @param            $amount
     * @param string     $operate_type
     * @param null       $remark
     * @param null       $tx_hash
     *
     * @return mixed
     * @throws LogicException
     */
    public static function BalancesChange(
        $orderNo,
        int $uid,
        AssetsType $assetType,
        string $assets_name,
        $amount,
        string $operate_type,
        $remark = null,
        $tx_hash = null
    ) {
        $info = self::changeWithoutLog($uid, $assetType, $amount);
        //写入日志
        $balancesLogs = new AssetsLogs();
        $balancesLogs->assets_type_id = $assetType->id;
        $balancesLogs->assets_name = $assets_name;
        $balancesLogs->uid = $uid;
        $balancesLogs->operate_type = $operate_type;
        $balancesLogs->amount = $amount;
        $balancesLogs->amount_before_change = $info[ 'amount_before_change' ];
        $balancesLogs->tx_hash = $tx_hash;
        $balancesLogs->ip = request()->server('HTTP_ALI_CDN_REAL_IP', request()->ip());
        $balancesLogs->user_agent = isset($_SERVER[ 'HTTP_USER_AGENT' ]) ? mb_substr($_SERVER[ 'HTTP_USER_AGENT' ], 0,
                                                                                     255, 'utf-8') : '';
        $balancesLogs->remark = $remark;
        $balancesLogs->order_no = $orderNo;
        $balancesLogs->save();
        return $balancesLogs->id;
    }
    
    /**
     * 检查托管地址余额.
     *
     * @param $num
     *
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
        $qkNode = new QkNodeRPC($urlArr[ 'host' ], $urlArr[ 'port' ]);
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
        $qkNode = new QkNodeRPC($urlArr[ 'host' ], $urlArr[ 'port' ]);
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
    
    //iets提现
    public function AssetsTransfer($amount, $address, $assetsTypeNmae)
    {
        //合约地址
        $asset = AssetsType::where('assets_name', $assetsTypeNmae)->first();
        $contract = $asset->contract_address;
        $urlArr = parse_url(env('WITHDRAW_RPC_HOST'));
        //实例化节点对象
        $qkNode = new QkNodeRPC($urlArr[ 'host' ], $urlArr[ 'port' ]);
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
    
    /**
     * Description:修改USDT余额
     *
     * @param int $change_times
     *
     * @return bool
     * @author lidong<947714443@qq.com>
     * @date   2021/8/11 0011
     */
    public function exchangeUSDTRatio($change_times = 1)
    {
        $sql = "UPDATE assets SET amount = (amount * 6.5 ), freeze_amount = (freeze_amount * 6.5),  change_times = (change_times+1) WHERE assets_type_id = 3 and change_times <{$change_times};";
        try {
            DB::getPdo()->exec($sql);
        } catch (\Exception $e) {
            echo $e->getMessage();
            return false;
        }
        return true;
    }
    
    /**
     * Description:重置修改次数
     *
     * @return bool
     * @author lidong<947714443@qq.com>
     * @date   2021/8/12 0012
     */
    public function resetChangeTimes()
    {
        $sql = "UPDATE assets SET change_times = 0 WHERE assets_type_id = 3 ;";
        try {
            DB::getPdo()->exec($sql);
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }
}
