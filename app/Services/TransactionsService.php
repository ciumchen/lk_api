<?php

namespace App\Services;

use App\Exceptions\LogicException;
use App\Models\Address;
use App\Models\AssetsLogs;
use App\Models\AssetsType;
use App\Models\Setting;
use App\Models\Transactions;
use App\Models\WithdrawLogs;
use ERC20\ERC20;
use EthereumRPC\EthereumRPC;
use EthereumRPC\Response\TransactionInputTransfer;
use Illuminate\Support\Facades\DB;

class TransactionsService
{
    const LOCK_KEY = 'tx-hash-sync';

    /**
     * 定时任务是否锁定
     */
    protected function isLock($key)
    {
        return file_exists(storage_path($key)) ? true : false;
    }

    /**
     * 锁定定时任务
     */
    protected function lock($key)
    {
        file_put_contents(storage_path($key), '1');
    }

    /**
     * 解锁定时任务
     */
    protected function unlock($key)
    {
        if ($this->isLock($key)) {
            unlink(storage_path($key));
        }
    }

    /**
     * 同步交易
     * @throws \Exception
     */
    public function synchronizeTransactionLogs()
    {
        $this->lock(self::LOCK_KEY);
        ini_set('max_execution_time', 60);
        $end_time = time() + 55;
        while (true) {
            if ($end_time <= time()) {
                break;
            }
            $this->syncTx();
            sleep(5);
        }
        $this->unlock(self::LOCK_KEY);

        echo "区块同步成功 \n";
    }

    /**
     * 同步官方托管地址的交易记录
     * @throws \Exception
     */
    public function syncTx()
    {
        //获取已同步的最大高度
        $last_block_height = Setting::where('key', 'last_block_height')->first();

        if (!$last_block_height) {
            $last_block_height = new Setting();
            $last_block_height->key = 'last_block_height';
            $last_block_height->value = 5992947;
            $last_block_height->save();
        }

        $lastBlock = $last_block_height->value;

        //获取区块当前最后一个高度
        $real_last_block = (new RpcService())->rpc('eth_getBlockByNumber', [['latest', true]]);

        if (isset($real_last_block[0]['result']['number']) && $real_last_block[0]['result']['number']) {
            $real_last_block = base_convert($real_last_block[0]['result']['number'], 16, 10) ?? 0;
        } else {
            $real_last_block = 0;
        }

        echo "当前最高高度：$real_last_block\n";

        //一次拿500个区块的交易
        $num = 500;
        if ($real_last_block) {
            if ($lastBlock + 10 >= $real_last_block) {
                $num = 10;
            }
        }
        for ($i = 0; $i < $num; $i++) {
            //组装参数
            if ($lastBlock < 10) {
                $blockArray[$i] = ['0x' . $lastBlock, true];
            } else {
                $blockArray[$i] = ['0x' . base_convert($lastBlock, 10, 16), true];
            }

            $lastBlock++;
        }

        //获取下一个区块
        $rpcService = new RpcService();
        try{
            $blocks = $rpcService->getBlockByNumber($blockArray);
        }
        catch (\Exception $exception)
        {
            echo "请求接口超时 \n";
            $this->unlock(self::LOCK_KEY);
        }

        DB::beginTransaction();
        try {

            $block_height = $last_block_height->value;
            if ($blocks) {
                echo "区块获取成功 \n";

                foreach ($blocks as $block) {
                    if ($block['result']) {

                        $block_time = base_convert($block['result']['timestamp'],16,10);
                        //太新的区块，不处理,至少要求30秒钟以上
                        if(time() - $block_time < 30)
                        {
                            break;
                        }

                        $transactions = $block['result']['transactions'];
                        //如果此区块有交易
                        if (isset($transactions) && count($transactions) > 0) {
                            $timestamp = date("Y-m-d H:i:s", base_convert($block['result']['timestamp'], 16, 10));
                            foreach ($transactions as $tx) {
                                //排除BT的交易
                                if($tx['to'] != "0x3fb708e854041673433e708fedb9a1b43905b6f7" && !Transactions::where('hash',$tx['hash'])->exists()) {
                                    $this->saveTx($tx, $timestamp);
                                }
                            }
                        }

                        $block_height = bcadd(base_convert($block['result']['number'], 16, 10), 1, 0);
                    } else {
                        $this->i = 10;
                        Setting::where('key', 'last_block_height')->update(['value' => $block_height]);
                        DB::commit();
                        echo "同步成功，当前高度:$block_height \n";
                        return false;
                    }
                }
            }

            //记录下一个要同步的区块高度
            Setting::where('key', 'last_block_height')->update(['value' => $block_height]);
            DB::commit();
            echo "同步成功，当前高度:$block_height\n";
            return true;
        } catch (\Exception $e) {
            DB::rollback();
            echo $e->getMessage()."\n".$e->getFile()."\n".$e->getLine()."\n";
            return false;
        }
    }

    /**
     * 保存交易
     * @param $v
     * @param $timestamp
     */
    public function saveTx($v, $timestamp)
    {

        //查询交易是否成功
        $receipt = (new RpcService())->rpc("eth_getTransactionReceipt", [[$v['hash']]]);
        if (isset($receipt[0]['result'])) {
            if(isset($receipt[0]['result']['root']))
            {
                $tx_status = Transactions::TX_STATUS_DEFAULT;
            }else{
                $tx_status = base_convert($receipt[0]['result']['status'], 16, 10);
            }
            if($tx_status != Transactions::TX_STATUS_DEFAULT)
            {
                echo "{$v['hash']}交易失败\n";
                return true;
            }
        }else{
            echo "{$v['hash']}没有回执\n";
            return true;
        }

        if($v['from'] == '0x090d0472f65bbb301eae0774c2e54d6d61c0fb2b')
            return false;

        //写入交易记录表
        $tx = new Transactions();
        $tx->from = $v['from'];
        $tx->to = $v['to'] ?? '';
        $tx->uid = 0;
        $tx->hash = $v['hash'];
        $tx->block_hash = $v['blockHash'];
        $tx->block_number = base_convert($v['blockNumber'], 16, 10);
        $tx->gas_price = bcdiv(number_format(hexdec($v['gasPrice']), 0, '', ''), gmp_pow(10, 18), 18);
        $tx->amount = bcdiv(number_format(hexdec($v['value']), 0, '', ''), gmp_pow(10, 18), 18);
        $tx->created_at = $timestamp;
        $tx->tx_status = Transactions::TX_STATUS_SUCCESS;

        //input可能为空
        $input = $v['input'] ?? '';

        // 通证转账
        if (substr($input, 0, 10) === '0xa9059cbb' && strlen($v['to']) == 42) {
            //实例化通证,获取通证小数位数
            $url_arr = parse_url(env("WITHDRAW_RPC_HOST"));
            $geth = new EthereumRPC($url_arr['host'], $url_arr['port']??null);
            $erc20 = new ERC20($geth);
            $token = $erc20->token($v['to']);
            $decimals = $token->decimals();
            if($decimals < 1)
                return true;
            //保存通证交易
            $token_tx = new TransactionInputTransfer($input);
            //判断to是否为cct和usdt的合约地址，如果是则添加
            $assets = AssetsType::where('contract_address', $v['to'])->first();
            if ($assets) {
                $decimals = ($assets->decimals > 0) ? $assets->decimals : $decimals;
                $token_tx_amount = bcdiv(number_format(hexdec($token_tx->amount), 0, '', ''), gmp_pow(10,$decimals), 18);
                //是通证，保存通证信息
                $tx->token_tx_amount = $token_tx_amount;
                $tx->assets_type = $assets->assets_name;
                $tx->assets_id = $assets->id;
                $tx->payee = $token_tx->payee;
            } else {
                echo "{$v['hash']}不支持资产\n";
                return true;
            }
        } else {
            //不是通证，则是qki
            $tx->assets_type = 'qki';
        }

        //根据转入地址判断用户ID
        $address = Address::where('address',$v['from'])->first();

        //要先绑定地址
        if($address && bccomp(strtotime($address->created_at),$timestamp,0) > 0)
            $tx->uid = $address->uid;

        //接收地址为托管地址，才保存交易
        if($tx->payee == env('WITHDRAW_ADDRESS') || $tx->to == env('WITHDRAW_ADDRESS'))
        {
            return $tx->save();
        }

        return $tx;
    }

    /**
     * 充值
     * @return bool
     */
    public function tokenCharge()
    {
        //获取未处理的、类型为转入、24小时内的记录
        $timeline = date("Y-m-d H:i:s",bcsub(time(),86400,0));

        //信任地址传入不需要等待
        //获取未处理的、类型为转入、状态为成功的记录
        $logs = Transactions::where('status', 1)
            ->where("tx_status", 1)
            ->where('created_at', '>', $timeline)
            ->get();

        if (!$logs) {
            //无数据，终止
            echo "无充值数据\n";
            return false;
        }

        foreach ($logs as $log) {
            $this->doTokenRecharge($log);
        }

        echo "token充值操作执行成功\n";
        return true;
    }


    /**
     * 充值
     * @param $log
     */
    public function doTokenRecharge($log)
    {
        //根据转入地址判断用户ID
        $address = Address::where('address',$log->from)->first();
        if (!$address) {
            //找不到用户，不执行操作
            echo "找不到用户\n";
            return;
        }
        if (bccomp($log->token_tx_amount, 0, 18) < 1) {
            //金额小于等于0，不执行操作
            echo "金额小于等于0\n";
            return;
        }
        if (AssetsLogs::where('tx_hash', $log->hash)->count()) {
            //hash是否存在于资产记录，则不执行操作
            echo "存在于资产记录\n";
            return;
        }

        $assets = AssetsType::find($log->assets_id);
        if($assets->can_withdraw == AssetsType::CANT_RECHARGE)
        {
            echo "该资产类型不支持充值\n";
            return;
        }

        DB::beginTransaction();
        try {
            $data_id = AssetsService::balancesChange($address->uid, $assets, $assets->assets_name, $log->token_tx_amount, 'recharge', '用户充值', $log->hash);
            //修改交易记录信息
            $transactionLog = Transactions::lockForUpdate()->find($log->id);
            $transactionLog->status = Transactions::STATUS_DONE;
            $transactionLog->deal_type = 'recharge';
            $transactionLog->data_id = $data_id;
            $transactionLog->uid = $address->uid;
            $transactionLog->save();

            DB::commit();
        } catch
        (\Exception $e) {
            DB::rollback();
            echo "token充值失败，失败原因：" . $e->getMessage();
        }
    }
}
