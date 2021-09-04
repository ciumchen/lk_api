<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Exceptions\LogicException;

/**
 * App\Models\TradeOrder
 *
 * @property int $id
 * @property int|null $user_id 买家id
 * @property string|null $title 商品标题
 * @property string $price 商品价格
 * @property int|null $num 购买数量
 * @property string|null $numeric 话费：手机号；美团、油卡：卡号
 * @property string|null $telecom 话费充值运营商
 * @property string|null $pay_time 付款时间
 * @property string|null $end_time 结束时间
 * @property string|null $modified_time 最后更新时间
 * @property string $status 交易状态：await 待支付；pending 支付处理中； succeeded 支付成功；failed 支付失败
 * @property string $order_from 订单来源：alipay；wx
 * @property string $order_no 订单号
 * @property string $need_fee 支付金额
 * @property string $profit_ratio 让利比例
 * @property string $profit_price 实际让利金额
 * @property string $integral 订单用户积分
 * @property string|null $description 支付附加说明：MT - 美团；HF - 话费；YK - 油卡
 * @property int|null $oid order表 -- id
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property string|null $remarks 美团卡用户姓名
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder|TradeOrder newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TradeOrder newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TradeOrder query()
 * @method static \Illuminate\Database\Eloquent\Builder|TradeOrder whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TradeOrder whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TradeOrder whereEndTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TradeOrder whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TradeOrder whereIntegral($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TradeOrder whereModifiedTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TradeOrder whereNeedFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TradeOrder whereNum($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TradeOrder whereNumeric($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TradeOrder whereOid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TradeOrder whereOrderFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TradeOrder whereOrderNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TradeOrder wherePayTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TradeOrder wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TradeOrder whereProfitPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TradeOrder whereProfitRatio($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TradeOrder whereRemarks($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TradeOrder whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TradeOrder whereTelecom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TradeOrder whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TradeOrder whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TradeOrder whereUserId($value)
 * @mixin \Eloquent
 */
class TradeOrder extends Model
{

    use HasFactory;

    protected $table = 'trade_order';
    protected $fillable = [
        'user_id',
        'title',
        'price',
        'num',
        'numeric',
        'telecom',
        'pay_time',
        'end_time',
        'modified_time',
        'status',
        'order_from',
        'order_no',
        'need_fee',
        'profit_ratio',
        'profit_price',
        'integral',
        'description',
        'oid',
        'created_at',
        'updated_at',
        'remarks',
        'idcard',
        'user_name'
    ];

    /**
     * 生成订单号
     *
     * @return string
     */
    public function CreateOrderNo()
    {
        return "PY_" . date("YmdHis") . rand(100000, 999999);
    }

    /**生成订单
     *
     * @param array $data
     *
     * @return bool
     * @throws
     */
    public function setOrder(array $data)
    {
        return DB::table($this->table)->insert($data);
    }

    /**检查是否已经支付
     *
     * @param int $uid
     *
     * @return array
     * @throws
     */
    public function checkOrderPay(int $uid)
    {
        $res = DB::table($this->table)->where([['user_id', '=', $uid], ['status', '=', 'await']])->get();
        if (!$res)
            throw new LogicException('请先下单');
        if (count($res) > 0) {
            return ['code' => 1, 'msg' => '订单待支付'];
        } else {
            return ['code' => -1, 'msg' => '订单已支付'];
        }
    }

    /**更新订单状态
     *
     * @param array $data
     *
     * @throws
     */
    public function upTradeOrder(array $data)
    {
        $orderInfo = DB::table('trade_order')->where('order_no', '=', $data[ 'order_no' ])->get()->toArray();
        $order = [];
        foreach ($orderInfo as $val) {
            $order = $val;
        }
        $orderData = get_object_vars($order);
        if (!$orderInfo)
            throw new LogicException('订单不存在');
        if ($orderData[ 'status' ] != 'await')
            throw new LogicException('订单已支付');
        $orderIntegral = 0;
        //计算让利金额
        $profitPrice = $orderData[ 'need_fee' ] * $orderData[ 'profit_ratio' ];
        try {
            if ($data[ 'status' ] == 'succeeded' && !in_array($orderData[ 'status' ], ['pending', 'succeeded'])) {
                //更新订单表
                $upData = [
                    'status'       => $data[ 'status' ],
                    'profit_price' => $profitPrice,
                    'integral'     => $orderIntegral,
                    'pay_time'     => $data[ 'pay_time' ],
                    'end_time'     => $data[ 'end_time' ],
                ];
                DB::table('trade_order')->where('order_no', $data[ 'order_no' ])->update($upData);
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**获取用户信息
     *
     * @param string $orderNo
     *
     * @return array
     * @throws
     */
    public function userInfo(string $orderNo)
    {
        return DB::table($this->table)->where('order_no', $orderNo)->get()->toArray();
    }

    /**获取订单信息
     *
     * @param string $orderNo
     *
     * @return mixed
     * @throws
     */
    public function tradeOrderInfo(string $orderNo)
    {
        return DB::table($this->table)->where('order_no', $orderNo)->first();
    }

    /**获取用户信息
     *
     * @param string $orderNo
     *
     * @return array
     * @throws
     */
    public function getUser(string $orderNo)
    {
        return DB::table($this->table)->join('users', function ($join) use ($orderNo) {
            $join->on('trade_order.user_id', '=', 'users.id')
                 ->where(['trade_order.order_no' => $orderNo, 'users.status' => 1]);
        })->get()->first();
    }

    /** 支付失败再次支付
     *
     * @param string $oid
     *
     * @return mixed
     * @throws
     */
    public function getOrderInfo(string $oid)
    {
        return DB::table($this->table)->where('oid', $oid)->first();
    }

    /**计算用户当月消费额
     *
     * @param array $data
     *
     * @return mixed
     * @throws
     */
    public function getMonthSum(array $data)
    {
        $firstday = date('Y-m-01 00:00:00', strtotime(date("Y-m-d")));
        $lastday = date('Y-m-d 23:59:59', strtotime("$firstday +1 month -1 day"));
        $data[ 'firstday' ] = $firstday;
        $data[ 'lastday' ] = $lastday;
        //返回
        return (new TradeOrder())
            ->where(function ($query) use ($data) {
                $query->where('user_id', $data[ 'uid' ])
                      ->where('description', $data[ 'description' ])
                      ->where('status', 'succeeded')
                      ->whereBetween('created_at', [$data[ 'firstday' ], $data[ 'lastday' ]]);
            })->sum('price');
    }
}
