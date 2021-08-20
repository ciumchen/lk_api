<?php

namespace App\Models;

use App\Http\Controllers\API\Message\UserMsgController;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Exceptions\LogicException;

/**
 * Class Order
 *
 * @property int                                  $id
 * @property int                                  $uid                  消费者UID
 * @property int                                  $business_uid         商家UID
 * @property string                               $profit_ratio         让利比列(%)
 * @property string                               $price                消费金额
 * @property string                               $profit_price         实际让利金额
 * @property int                                  $status               1审核中，2审核通过，3审核失败
 * @property string                               $name                 消费商品名
 * @property string|null                          $remark               备注
 * @property \Illuminate\Support\Carbon|null      $created_at
 * @property \Illuminate\Support\Carbon|null      $updated_at
 * @property int                                  $state                盟主订单标识,1非盟主订单，2盟主订单
 * @property string|null                          $pay_status           支付状态：await 待支付；pending 支付处理中； succeeded
 *           支付成功；failed 支付失败,ddyc订单异常
 * @property string|null                          $to_be_added_integral 用户待加积分
 * @property int|null                             $to_status            订单处理状态：默认0,1表示待处理,2表示已处理
 * @property int|null                             $line_up              排队状态,默认0不排队,1表示排队
 * @property string                               $order_no             斑马充值订单号
 * @property-read \App\Models\TradeOrder          $Trade_Order
 * @property-read \App\Models\OrderVideo          $video
 * @property-read \App\Models\OrderAirTrade       $air
 * @property-read \App\Models\OrderMobileRecharge $mobile
 * @property-read \App\Models\TradeOrder          $trade
 * @property-read \App\Models\OrderUtilityBill    $utility
 * @property-read \App\Models\BusinessData|null   $business
 * @property-read mixed                           $updated_date
 * @property-read \App\Models\User|null           $user
 * @method static Builder|Order newModelQuery()
 * @method static Builder|Order newQuery()
 * @method static Builder|Order query()
 * @method static Builder|Order whereBusinessUid($value)
 * @method static Builder|Order whereCreatedAt($value)
 * @method static Builder|Order whereId($value)
 * @method static Builder|Order whereLineUp($value)
 * @method static Builder|Order whereName($value)
 * @method static Builder|Order whereOrderNo($value)
 * @method static Builder|Order wherePayStatus($value)
 * @method static Builder|Order wherePrice($value)
 * @method static Builder|Order whereProfitPrice($value)
 * @method static Builder|Order whereProfitRatio($value)
 * @method static Builder|Order whereRemark($value)
 * @method static Builder|Order whereState($value)
 * @method static Builder|Order whereStatus($value)
 * @method static Builder|Order whereToBeAddedIntegral($value)
 * @method static Builder|Order whereToStatus($value)
 * @method static Builder|Order whereUid($value)
 * @method static Builder|Order whereUpdatedAt($value)
 * @mixin \Eloquent
 * @package App\Models
 * @property-read \App\Models\LkshopOrder|null    $lkshopOrder
 * @property-read \App\Models\ConvertLogs|null    $convertLogs
 * @property-read \App\Models\OrderHotel|null $hotel
 * @property int|null $import_day 导入日期
 * @method static Builder|Order whereImportDay($value)
 * @property int|null $member_gl_oid 购买来客会员邀请人订单关联用户订单oid
 * @method static Builder|Order whereMemberGlOid($value)
 */
class Order extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'order';

    const STATUS_DEFAULT = 1;//审核中

    const STATUS_SUCCEED = 2;//审核通过

    const STATUS_FAILED  = 3;//审核不通过

    /**
     * 类型文本.
     *
     * @var array
     */
    public static $statusLabel = [
        self::STATUS_DEFAULT => '正在审核',
        self::STATUS_SUCCEED => '审核通过',
        self::STATUS_FAILED  => '审核不通过',
    ];

    /**店铺关联
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function business()
    {
        return $this->hasOne(BusinessData::class, 'uid', 'business_uid');
    }

    /**用户关联
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function user()
    {
        return $this->hasOne(User::class, 'id', 'uid');
    }

    /**获取商家信息
     *
     * @param string $orderNo
     *
     * @return array
     * @throws
     */
    public function getShop(string $orderNo)
    {
        $tradeOrderInfo = DB::table('trade_order')
                            ->where('order_no', $orderNo)
                            ->first();
        $orders = get_object_vars($tradeOrderInfo);
        $orderData = DB::table($this->table)
                       ->where('id', $orders[ 'oid' ])
                       ->first();
        return get_object_vars($orderData);
    }

    /**插入用户积分流水记录
     *
     * @param array  $usersData
     * @param string $beforeIntegral
     * @param string $integral
     *
     * @throws
     */
    private function setIntegral(array $usersData, string $beforeIntegral, string $integral)
    {
        $date = date("Y-m-d H:i:s");
        //插入积分流水记录
        $integralData = [
            'uid'                  => $usersData[ 'id' ],
            'operate_type'         => 'spend',
            'role'                 => $usersData[ 'role' ],
            'amount_before_change' => $beforeIntegral,
            'amount'               => $integral,
            'created_at'           => $date,
            'updated_at'           => $date,
        ];
        $integralData[ 'remark' ] = $usersData[ 'role' ] == 1 ? '消费者完成订单' : '商家完成订单';
        DB::table('integral_log')
          ->insert($integralData);
    }

    /**插入美团、油卡、话费记录
     *
     * @param array $data
     *
     * @return int
     * @throws
     */
    public function setOrder(array $data)
    {
        return DB::table($this->table)
                 ->insertGetId($data);
    }

    /**
     * Description: 批量代充
     *
     * @param int    $uid
     * @param string $money
     * @param string $order_no
     *
     * @return $this
     * @throws \Exception
     * @author lidong<947714443@qq.com>
     * @date   2021/7/5 0005
     */
    public function setManyMobileOrder($uid, $money, $order_no)
    {
        try {
            $profit_ratio = Setting::getSetting('set_business_rebate_scale_zl');
            $profit_price = (float) $money * ($profit_ratio / 100);
            $business_uid = 2;
            $name = '批量代充';
            $this->setOrderSelf($uid, $business_uid, $profit_ratio, $money, $profit_price, $order_no, $name);
        } catch (Exception $e) {
            throw $e;
        }
        return $this;
    }

    /**
     * Description: 生成订单
     *
     * @param int    $uid
     * @param int    $business_uid
     * @param string $profit_ratio
     * @param string $price
     * @param string $profit_price
     * @param string $order_no
     * @param string $name
     * @param int    $state
     * @param string $pay_status
     * @param string $remark
     *
     * @return $this
     * @throws \Exception
     * @author lidong<947714443@qq.com>
     * @date   2021/7/2 0002
     */
    public function setOrderSelf(
        $uid,
        $business_uid,
        $profit_ratio,
        $price,
        $profit_price,
        $order_no,
        $name,
        $state = 1,
        $pay_status = 'await',
        $remark = ''
    ) {
        try {
            $this->uid = $uid;
            $this->business_uid = $business_uid;
            $this->profit_ratio = $profit_ratio;
            $this->price = $price;
            $this->profit_price = $profit_price;
            $this->status = 1;
            $this->name = $name;
            $this->remark = $remark;
            $this->state = $state;
            $this->pay_status = $pay_status;
            $this->order_no = $order_no;
            $this->save();
        } catch (Exception $e) {
            throw $e;
        }
        return $this;
    }

    /**获取当天未支付订单
     *
     * @param array $data
     *
     * @return mixed
     * @throws
     */
    public function getTodayOrders()
    {
        $year = date("Y");
        $month = date("m");
        $day = date("d");
        //当天开始时间
        $start = date("Y-m-d H:i:s", mktime(0, 0, 0, $month, $day, $year));
        //当天结束时间
        $end = date("Y-m-d H:i:s", mktime(23, 59, 59, $month, $day, $year));
        $data[ 'start' ] = $start;
        $data[ 'end' ] = $end;
        //返回
        return (new Order())
            ->where(
                function ($query) use ($data) {
                    $query->where('pay_status', 'await')
                          ->whereBetween('created_at', [$data[ 'start' ], $data[ 'end' ]]);
                }
            )
            ->get()
            ->toArray();
    }

    /**录单消息通知
     *
     * @param string $orderNo
     *
     * @return mixed
     * @throws
     */
    public function orderMsg(string $orderNo)
    {
        $orderDataInfo = (new Order())
            ->join(
                'trade_order',
                function ($join) {
                    $join->on('order.id', 'trade_order.oid');
                }
            )
            ->where(
                function ($query) use ($orderNo) {
                    $query->where('trade_order.order_no', $orderNo)
                          ->where('order.pay_status', 'succeeded')
                          ->where('order.status', 2);
                }
            )
            ->get(['order.*', 'trade_order.numeric']);
        if (!$orderDataInfo) {
            throw new LogicException('订单录单未通过');
        }
        //添加消息通知
        (new UserMsgController())->setMsg($orderNo, 2);
    }

    /**机票消息通知
     *
     * @param string $orderNo
     *
     * @return mixed
     * @throws
     */
    public function airOrderMsg(string $orderNo)
    {
        $orderDataInfo = (new Order())
            ->join(
                'air_trade_logs',
                function ($join) {
                    $join->on('order.order_no', 'air_trade_logs.order_no');
                }
            )
            ->where(
                function ($query) use ($orderNo) {
                    $query->where('order.order_no', $orderNo)
                          ->where('order.pay_status', 'succeeded')
                          ->where('order.status', 2);
                }
            )
            ->get(['order.*']);
        if (!$orderDataInfo) {
            throw new LogicException('机票订单未支付或未审核通过');
        }
        //添加消息通知
        (new UserMsgController())->setAirMsg($orderNo, 4);
    }

    /**获取订单数据
     *
     * @param string $orderNo
     *
     * @return mixed
     * @throws
     */
    public function getOrderInfo(string $orderNo)
    {
        $res = (new Order())::where('order_no', $orderNo)
                            ->exists();
        if (!$res) {
            throw new LogicException('订单数据不存在');
        }
        return (new Order())::where('order_no', $orderNo)
                            ->get()
                            ->first();
    }

    /**机票再次支付
     *
     * @param string $orderNo
     *
     * @return mixed
     * @throws
     */
    public function airOrder(string $orderNo)
    {
        $date = date('Y-m-d H:i:s');
        //获取订单信息
        $orderData = (new Order())::where('order_no', $orderNo)
                                  ->get(['id', 'pay_status'])
                                  ->first();
        if (in_array($orderData[ 'pay_status' ], ['pending', 'succeeded'])) {
            throw new LogicException('订单不属于未支付或支付失败状态');
        }
        $aidData = (new AirTradeLogs())::where('order_no', $orderNo)
                                       ->get(['id'])
                                       ->first();
        $orderNo = 'PY_'.date('YmdHis').rand(100000, 999999);
        //更新order 表订单信息
        $order = (new Order())->find($orderData[ 'id' ]);
        $order->order_no = $orderNo;
        $order->updated_at = $date;
        $order->save();
        //更新air_trade_logs 表订单信息
        $airTradeLogs = (new AirTradeLogs())->find($aidData->id);
        $airTradeLogs->order_no = $orderNo;
        $airTradeLogs->updated_at = $date;
        $airTradeLogs->save();
        return $orderNo;
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uid',
        'business_uid',
        'profit_ratio',
        'price',
        'profit_price',
        'status',
        'name',
        'order_no',
        'member_gl_oid',
        'pay_status',
        'description',
    ];

    public function Trade_Order()
    {
        return $this->belongsTo(TradeOrder::class, 'id', 'oid');
    }

    public function getUpdatedDateAttribute($value)
    {
//        dd($value);
        return date("Y-m-d H:i:s", strtotime($this->attributes[ 'updated_at' ]));
    }

    /**
     * 生成水费订单
     *
     * @param int    $uid
     * @param float  $money
     * @param string $order_no
     *
     * @return \App\Models\Order
     * @throws \Exception
     */
    public function setWaterOrder($uid, $money, $order_no)
    {
        $profit_ratio = 5;
        $profit_price = $money * ($profit_ratio / 100);
        try {
            $this->uid = $uid;
            $this->business_uid = 2;
            $this->profit_ratio = $profit_ratio;
            $this->price = $money;
            $this->profit_price = $profit_price;
            $this->status = '1';
            $this->name = '水费充值';
            $this->remark = '';
            $this->state = '1';
            $this->pay_status = 'await';
            $this->order_no = $order_no;
            $this->save();
        } catch (Exception $e) {
            throw $e;
        }
        return $this;
    }

    /**
     * 生成水费订单
     *
     * @param int    $uid
     * @param float  $money
     * @param string $order_no
     *
     * @return \App\Models\Order
     * @throws \Exception
     */
    public function setElectricityOrder($uid, $money, $order_no)
    {
        $profit_ratio = 5;
        $profit_price = $money * ($profit_ratio / 100);
        try {
            $this->uid = $uid;
            $this->business_uid = '2';
            $this->profit_ratio = $profit_ratio;
            $this->price = $money;
            $this->profit_price = $profit_price;
            $this->status = '1';
            $this->name = '电费充值';
            $this->remark = '';
            $this->state = '1';
            $this->pay_status = 'await';
            $this->order_no = $order_no;
            $this->save();
        } catch (Exception $e) {
            throw $e;
        }
        return $this;
    }

    /**
     * 生成燃气费订单
     *
     * @param int    $uid
     * @param float  $money
     * @param string $order_no
     *
     * @return $this
     * @throws \Exception
     */
    public function setGasOrder($uid, $money, $order_no)
    {
        $profit_ratio = 5;
        $profit_price = $money * ($profit_ratio / 100);
        try {
            $this->uid = $uid;
            $this->business_uid = '2';
            $this->profit_ratio = $profit_ratio;
            $this->price = $money;
            $this->profit_price = $profit_price;
            $this->status = '1';
            $this->name = '燃气费充值';
            $this->remark = '';
            $this->state = '1';
            $this->pay_status = 'await';
            $this->order_no = $order_no;
            $this->save();
        } catch (Exception $e) {
            throw $e;
        }
        return $this;
    }

    /**
     * 视频会员充值
     *
     * @param $uid
     * @param $money
     * @param $order_no
     *
     * @return $this
     * @throws \Exception
     */
    public function setVideoOrder($uid, $money, $order_no)
    {
        $profit_ratio = Setting::getSetting('set_business_rebate_scale_vc');
        $profit_price = $money * ($profit_ratio / 100);
        try {
            $this->uid = $uid;
            $this->business_uid = '2';
            $this->profit_ratio = $profit_ratio;
            $this->price = $money;
            $this->profit_price = $profit_price;
            $this->status = '1';
            $this->name = '视频会员';
            $this->remark = '';
            $this->state = '1';
            $this->pay_status = 'await';
            $this->order_no = $order_no;
            $this->save();
        } catch (Exception $e) {
            throw $e;
        }
        return $this;
    }

    /**
     * Description:通过订单号获取订单信息
     *
     * @param $order_no
     *
     * @return \App\Models\Order|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|null
     * @author lidong<947714443@qq.com>
     * @date   2021/6/11 0011
     */
    public function getOrderByOrderNo($order_no)
    {
        return $this->where('order_no', '=', $order_no)
                    ->first();
    }

    public function lkshopOrder()
    {
        return $this->hasOne(LkshopOrder::class, 'oid', 'id');
    }

    /**
     * Description:TradeOrder表关联
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     * @author lidong<947714443@qq.com>
     * @date   2021/6/11 0011
     */
    public function trade()
    {
        return $this->hasOne(TradeOrder::class, 'oid', 'id');
    }

    /**
     * Description:视频会员订单关联模型
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     * @author lidong<947714443@qq.com>
     * @date   2021/6/11 0011
     */
    public function video()
    {
        return $this->hasOne(OrderVideo::class, 'order_id', 'id');
    }

    /**
     * Description:
     * TODO:机票订单关联模型
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     * @author lidong<947714443@qq.com>
     * @date   2021/6/11 0011
     */
    public function air()
    {
        return $this->hasOne(OrderAirTrade::class, 'oid', 'id');
    }

    /**
     * Description:斑马手机充值
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     * @author lidong<947714443@qq.com>
     * @date   2021/6/11 0011
     */
    public function mobile()
    {
        return $this->hasOne(OrderMobileRecharge::class, 'order_id', 'id');
    }

    /**机票更新支付状态
     *
     * @param string $order_no
     *
     * @throws
     */
    public function updPay(string $order_no)
    {
        $res = Order::where(['order_no' => $order_no, 'pay_status' => 'await'])->exists();
        if (!$res) {
            throw new LogicException('订单不属于待支付状态');
        }
        Order::where('order_no', $order_no)->update(['pay_status' => 'succeeded']);
    }

    /**
     * Description:斑马生活缴费
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     * @author lidong<947714443@qq.com>
     * @date   2021/6/15 0015
     */
    public function utility()
    {
        return $this->hasOne(OrderUtilityBill::class, 'order_id', 'id');
    }

    /**
     * Description:碎片兑换订单
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     * @author lidong<947714443@qq.com>
     * @date   2021/7/15 0015
     */
    public function convertLogs()
    {
        return $this->hasOne(ConvertLogs::class, 'oid', 'id');
    }

    /**
     * Description:
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     * @author lidong<947714443@qq.com>
     * @date   2021/6/29 0029
     */
    public function hotel()
    {
        return $this->hasOne(OrderHotel::class, 'order_id', 'id');
    }


    /**新增拼团未中奖用户录单
     * @param array $data
     * @return mixed
     * @throws LogicException
     */
    public function setGatherOrder (array $data)
    {
        return Order::insert($data);
    }

    /**获取拼团用户录单信息
     * @param array $data
     * @return mixed
     * @throws LogicException
     */
    public function getGatherOrder (array $data)
    {
        return Order::whereIn('order_no', $data)
                ->get(['id as oid', 'order_no', 'uid']);
    }

    /**
     * Description: 格式化时间字段
     *
     * @param $value
     *
     * @return mixed|string
     * @author lidong<947714443@qq.com>
     * @date   2021/6/29 0029
     */

    public function getCreatedAtAttribute($value)
    {
        if ($value) {
            $value = Carbon::createFromFormat(
                'Y-m-d\TH:i:s.vv\Z',
                date('Y-m-d\TH:i:s.vv\Z', strtotime($value))
            )
                           ->format('Y-m-d H:i:s');
        }
        return $value;
    }

    /**
     * Description: 格式化时间字段
     *
     * @param $value
     *
     * @return mixed|string
     * @author lidong<947714443@qq.com>
     * @date   2021/6/29 0029
     */

    public function getUpdatedAtAttribute($value)
    {
        if ($value) {
            $value = Carbon::createFromFormat(
                'Y-m-d\TH:i:s.vv\Z',
                date('Y-m-d\TH:i:s.vv\Z', strtotime($value))
            )
                           ->format('Y-m-d H:i:s');
        }
        return $value;
    }
}
