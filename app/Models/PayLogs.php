<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Exceptions\LogicException;

/**
 * App\Models\PayLogs
 *
 * @property int $id
 * @property string|null $pid 支付对象PID
 * @property int|null $uid 用户UID
 * @property string|null $order_no 订单号
 * @property string|null $pay_channel 支付渠道
 * @property string $pay_amt 交易金额
 * @property string $description 支付附加说明：MT - 美团；HF - 话费；YK - 油卡
 * @property string $party_order_id 商户订单号
 * @property string $out_trans_id 交易订单号
 * @property string $status 订单状态：await 待支付；pending 支付处理中； succeeded 支付成功；failed 支付失败
 * @property string $created_time 支付创建时间
 * @property string $end_time 支付完成时间
 * @method static \Illuminate\Database\Eloquent\Builder|PayLogs newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PayLogs newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PayLogs query()
 * @method static \Illuminate\Database\Eloquent\Builder|PayLogs whereCreatedTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PayLogs whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PayLogs whereEndTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PayLogs whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PayLogs whereOrderNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PayLogs whereOutTransId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PayLogs wherePartyOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PayLogs wherePayAmt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PayLogs wherePayChannel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PayLogs wherePid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PayLogs whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PayLogs whereUid($value)
 * @mixin \Eloquent
 */
class PayLogs extends Model
{
    protected $table = 'pay_logs';

    /**交易数据写入
     * @param array $data
     * @return bool
     */
    public function setPay(array $data)
    {
        return DB::table($this->table)->insert($data);
    }

    /**获取机票订单信息
     * @param string $orderNo
     * @return mixed
     * @throws
     */
    public function payInfo(string $orderNo)
    {
        $res = DB::table($this->table)->where('order_no', $orderNo)->exists();
        if (!$res)
        {
            throw new LogicException('订单信息不存在');
        }

        return DB::table($this->table)->where(['order_no' => $orderNo, 'status' => 'succeeded'])->get('order_no');
    }
}
