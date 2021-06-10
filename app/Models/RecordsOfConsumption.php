<?php

namespace App\Models;

use App\Http\Controllers\API\Message\UserMsgController;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Exceptions\LogicException;
use App\Models\Users;

/**
 * App\Models\RecordsOfConsumption
 *
 * @property int $id
 * @property int $uid uid
 * @property string $operate_type 操作类型
 * @property string $amount 变动数量
 * @property string $amount_before_change 变动前数量
 * @property int $role 1普通用户，2商家
 * @property string|null $ip ip
 * @property string|null $user_agent ua
 * @property string|null $remark 备注
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $order_no trade_order表 -- order_no
 * @property int|null $activityState 积分活动状态,0表示关闭,1标识开启
 * @property int|null $consumer_uid 贡献积分的消费者uid
 * @property string|null $description 订单类型
 * @property-read mixed $updated_date
 * @property-read Users|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|RecordsOfConsumption newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RecordsOfConsumption newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RecordsOfConsumption query()
 * @method static \Illuminate\Database\Eloquent\Builder|RecordsOfConsumption whereActivityState($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecordsOfConsumption whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecordsOfConsumption whereAmountBeforeChange($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecordsOfConsumption whereConsumerUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecordsOfConsumption whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecordsOfConsumption whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecordsOfConsumption whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecordsOfConsumption whereIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecordsOfConsumption whereOperateType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecordsOfConsumption whereOrderNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecordsOfConsumption whereRemark($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecordsOfConsumption whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecordsOfConsumption whereUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecordsOfConsumption whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecordsOfConsumption whereUserAgent($value)
 * @mixin \Eloquent
 */
class RecordsOfConsumption extends Model
{

    protected $table = 'integral_log';
    protected $appends = ['updated_date'];


    public function user()
    {
        return $this->belongsTo(Users::class, 'consumer_uid','id');
    }
    public function getUpdatedDateAttribute($value)
    {
//        dd($value);
        return date("Y-m-d H:i:s",strtotime($this->attributes['updated_at']));
    }

}
