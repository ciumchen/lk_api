<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
