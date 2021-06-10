<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\WithdrawLogs
 *
 * @property int $id
 * @property int $uid
 * @property int $assets_type_id 资金类型ID
 * @property string $assets_type 资金类型
 * @property string $address 地址
 * @property string $amount 数量
 * @property string $fee 手续费
 * @property string|null $tx_hash 交易HASH
 * @property int $status 1默认 2成功 3审核中 4拒绝
 * @property string $ip ip
 * @property string $remark 备注
 * @property string|null $user_agent
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|WithdrawLogs newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WithdrawLogs newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WithdrawLogs query()
 * @method static \Illuminate\Database\Eloquent\Builder|WithdrawLogs whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WithdrawLogs whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WithdrawLogs whereAssetsType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WithdrawLogs whereAssetsTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WithdrawLogs whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WithdrawLogs whereFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WithdrawLogs whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WithdrawLogs whereIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WithdrawLogs whereRemark($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WithdrawLogs whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WithdrawLogs whereTxHash($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WithdrawLogs whereUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WithdrawLogs whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WithdrawLogs whereUserAgent($value)
 * @mixin \Eloquent
 */
class WithdrawLogs extends Model
{
    protected $table = 'withdraw_logs';

    //状态 1默认 2成功 3待审核
    const STATUS_DEFAULT = 1;
    const STATUS_DONE = 2;
    const STATUS_TO_BE_VIEWED = 3;
    const STATUS_REFUSE = 4;

}
