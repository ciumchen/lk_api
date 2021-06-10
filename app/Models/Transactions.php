<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Transactions
 *
 * @property int $id
 * @property string $from 转出地址
 * @property string $to 转入地址
 * @property string|null $hash 转账hash
 * @property string|null $block_hash 区块hash
 * @property int $block_number 区块高度
 * @property string $gas_price 矿工费
 * @property string $amount 数量
 * @property int $status 状态，1默认，2已处理
 * @property int $tx_status 交易状态，1成功，0失败
 * @property int|null $assets_id 通证类型id
 * @property string $assets_type 资产类型
 * @property string|null $deal_type 处理类型  +充值recharge  -提现withdraw  -退回refund,处理完毕后再补全
 * @property int|null $data_id 处理对应的数据id，充值为assets_logs数据id、提现为withdraw_id、退回为refund_id
 * @property string|null $remark 备注
 * @property int|null $admin_id 如果是管理员操作，则填写此字段
 * @property string|null $payee 接收地址(通证)
 * @property string|null $token_tx_amount 通证交易数量
 * @property int $uid 用户id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Transactions newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Transactions newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Transactions query()
 * @method static \Illuminate\Database\Eloquent\Builder|Transactions whereAdminId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transactions whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transactions whereAssetsId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transactions whereAssetsType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transactions whereBlockHash($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transactions whereBlockNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transactions whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transactions whereDataId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transactions whereDealType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transactions whereFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transactions whereGasPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transactions whereHash($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transactions whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transactions wherePayee($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transactions whereRemark($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transactions whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transactions whereTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transactions whereTokenTxAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transactions whereTxStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transactions whereUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transactions whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Transactions extends Model
{
    use HasFactory;
    protected $table = "transactions";

    const STATUS_DEFAULT = 1;
    const STATUS_DONE = 2;

    const TX_STATUS_DEFAULT = 1;
    const TX_STATUS_SUCCESS = 2;
    const TX_STATUS_FAILED = 3;
}
