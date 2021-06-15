<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Assets
 *
 * @property int $id
 * @property int $uid uid
 * @property int $assets_type_id 资产类型ID
 * @property string $assets_name 资产名称
 * @property string $amount 数量
 * @property string $freeze_amount 冻结数量
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Assets $assetsType
 * @method static \Illuminate\Database\Eloquent\Builder|Assets newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Assets newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Assets query()
 * @method static \Illuminate\Database\Eloquent\Builder|Assets whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Assets whereAssetsName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Assets whereAssetsTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Assets whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Assets whereFreezeAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Assets whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Assets whereUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Assets whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Assets extends Model
{
    protected $table = 'assets';

    /**资产类型
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function assetsType(){

        return $this->belongsTo(Assets::class, 'assets_type_id');
    }

    /**
     * 变更余额.
     *
     * @param float  $amount
     * @param string $operateType
     * @param string $remark
     * @param array  $options
     *
     * @return void
     */
    public function change($amount, $operateType, $remark = '', array $options = [])
    {
        $assets = AssetsType::find($this->assets_type_id);
        $assetsLog = AssetsLogs::create([
            'assets_type_id' => $this->assets_type_id,
            'assets_name' => $assets->assets_name,
            'uid' => $this->uid,
            'operate_type' => $operateType,
            'amount' => $amount,
            'amount_before_change' => $this->getRawOriginal('amount'),
            'ip' => $options['ip'] ?? '',
            'user_agent' => $options['user_agent'] ?? '',
            'remark' => $remark,
        ]);

        $this->amount = bcadd($this->getRawOriginal('amount'), $assetsLog->amount, 8);
        $this->save();
    }
}
