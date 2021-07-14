<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\AssetsType
 *
 * @property int $id
 * @property string|null $contract_address 合约地址
 * @property string $assets_name 资产名称
 * @property int $recharge_status 是否可充值，1可充值，2不能充值
 * @property int $can_withdraw 是否能提现，1能，2不能
 * @property string $withdraw_fee 提现手续费（%）
 * @property string $large_withdraw_amount 提现审核额度
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static Builder|AssetsType newModelQuery()
 * @method static Builder|AssetsType newQuery()
 * @method static Builder|AssetsType query()
 * @method static Builder|AssetsType whereAssetsName($value)
 * @method static Builder|AssetsType whereCanWithdraw($value)
 * @method static Builder|AssetsType whereContractAddress($value)
 * @method static Builder|AssetsType whereCreatedAt($value)
 * @method static Builder|AssetsType whereId($value)
 * @method static Builder|AssetsType whereLargeWithdrawAmount($value)
 * @method static Builder|AssetsType whereRechargeStatus($value)
 * @method static Builder|AssetsType whereUpdatedAt($value)
 * @method static Builder|AssetsType whereWithdrawFee($value)
 * @mixin \Eloquent
 */
class AssetsType extends Model
{
    protected $table = 'assets_type';

    const DEFAULT_ASSETS_NAME = 'usdt';
    const ASSETS_NAME_USDT_TO_IETS = 'usdt_to_iets';
    const DEFAULT_ASSETS_ENCOURAGE = 'encourage';

    const CAN_WITHDRAW = 1;
    const CANT_WITHDRAW = 2;

    const CAN_RECHARGE = 1;
    const CANT_RECHARGE = 2;
}
