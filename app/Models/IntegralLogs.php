<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IntegralLogs extends Model
{
    protected $table = 'integral_log';
    const TYPE_SPEND = 'spend';
    const TYPE_REBATE = 'rebate';

    protected $appends = ['updated_date'];



    public static $typeLabel = [
        'consumption' => '消费增加',
        'Jangli' => '让利增加',
        self::TYPE_SPEND => '消费订单完成',
        self::TYPE_REBATE => '分红扣除积分',
    ];

    public static $rolLabel = [
        1 => '普通积分',
        2 => '商家积分',
    ];

    protected $fillable = [
        'uid',
        'amount',
        'amount_before_change',
        'operate_type',
        'role',
        'remark',
        'ip',
        'user_agent',
        'order_no',
        'activityState',
        'consumer_uid'
    ];
    /**
     * 用户信息
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'uid','id');
    }

    /**写入日志
     * @param $uid
     * @param $amount
     * @param $operateType
     * @param $amountBeforeChange
     * @param string $remark
     * @param $role
     */
    public static function addLog($uid, $amount, $operateType, $amountBeforeChange, $role, $remark = '',$orderNo='',$activityState=0,$consumer_uid)
    {

        self::create([
            'uid' => $uid,
            'amount' => $amount,
            'amount_before_change' => $amountBeforeChange,
            'operate_type' => $operateType,
            'role' => $role,
            'remark' => $remark,
            'ip' => '',
            'user_agent' => '',
            'order_no'=>$orderNo,
            'activityState'=>$activityState,
            'consumer_uid'=>$consumer_uid
        ]);

    }

    public function getUpdatedDateAttribute($value)
    {
//        dd($value);
        return date("Y-m-d H:i:s",strtotime($this->attributes['updated_at']));
    }

}
