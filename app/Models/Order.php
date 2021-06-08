<?php

namespace App\Models;

use App\Http\Controllers\API\Message\UserMsgController;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Exceptions\LogicException;

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
    const STATUS_FAILED = 3;//审核不通过

    /**
     * 类型文本.
     *
     * @var array
     */
    public static $statusLabel = [
        self::STATUS_DEFAULT => '正在审核',
        self::STATUS_SUCCEED => '审核通过',
        self::STATUS_FAILED => '审核不通过',
    ];

    /**店铺关联
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function business(){

        return $this->hasOne(BusinessData::class, 'uid', 'business_uid');
    }

    /**用户关联
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function user(){

        return $this->hasOne(User::class, 'id', 'uid');
    }

    /**获取商家信息
     * @param string $orderNo
     * @return array
     * @throws
     */
    public function getShop(string $orderNo)
    {
        $tradeOrderInfo = DB::table('trade_order')->where('order_no', $orderNo)->first();
        $orders = get_object_vars($tradeOrderInfo);
        $orderData = DB::table($this->table)->where('id', $orders['oid'])->first();
        return get_object_vars($orderData);
    }

    /**插入用户积分流水记录
     * @param array $usersData
     * @param string $beforeIntegral
     * @param string $integral
     * @throws
     */
    private function setIntegral(array $usersData, string $beforeIntegral, string $integral)
    {
        $date = date("Y-m-d H:i:s");
        //插入积分流水记录
        $integralData = [
            'uid' => $usersData['id'],
            'operate_type' => 'spend',
            'role' => $usersData['role'],
            'amount_before_change' => $beforeIntegral,
            'amount' => $integral,
            'created_at' => $date,
            'updated_at' => $date,
        ];

        $integralData['remark'] = $usersData['role'] == 1 ? '消费者完成订单' : '商家完成订单';
        DB::table('integral_log')->insert($integralData);
    }

    /**插入美团、油卡、话费记录
     * @param array $data
     * @return int
     * @throws
     */
    public function setOrder(array $data)
    {
        return DB::table($this->table)->insertGetId($data);
    }

    /**获取当天未支付订单
     * @param array $data
     * @return mixed
     * @throws
     */
    public function getTodayOrders()
    {
        $year = date("Y");
        $month = date("m");
        $day = date("d");
        //当天开始时间
        $start = date("Y-m-d H:i:s", mktime(0,0,0,$month,$day,$year));
        //当天结束时间
        $end= date("Y-m-d H:i:s", mktime(23,59,59,$month,$day,$year));
        $data['start'] = $start;
        $data['end'] = $end;

        //返回
        return (new Order())
            ->where(function($query) use ($data){
                $query->where('pay_status', 'await')
                    ->whereBetween('created_at', [$data['start'], $data['end']]);
            })->get()->toArray();
    }

    /**录单消息通知
     * @param string $orderNo
     * @return mixed
     * @throws
     */
    public function orderMsg(string $orderNo)
    {
        $orderDataInfo = (new Order())
            ->join('trade_order', function($join){
                $join->on('order.id', 'trade_order.oid');
            })
            ->where(function($query) use ($orderNo){
                $query->where('trade_order.order_no', $orderNo)
                    ->where('order.pay_status', 'succeeded')
                    ->where('order.status', 2);
            })
            ->get(['order.*', 'trade_order.numeric']);
        if (!$orderDataInfo)
        {
            throw new LogicException('订单录单未通过');
        }

        //添加消息通知
        (new UserMsgController())->setMsg($orderNo, 2);
    }

    /**获取订单数据
    * @param string $orderNo
    * @return mixed
    * @throws
    */
    public function getOrderInfo(string $orderNo)
    {
        $res = (new Order())::where('order_no', $orderNo)->exists();
        if (!$res)
        {
            throw new LogicException('订单数据不存在');
        }

        return (new Order())::where('order_no', $orderNo)->get()->first();
    }

    /**机票再次支付
    * @param string $orderNo
    * @return mixed
    * @throws
    */
    public function airOrder(string $orderNo)
    {
        $date = date('Y-m-d H:i:s');
        //获取订单信息
        $orderData = (new Order())::where('order_no', $orderNo)->get(['id', 'pay_status'])->first();
        if (in_array($orderData['pay_status'], ['pending', 'succeeded']))
        {
            throw new LogicException('订单不属于未支付或支付失败状态');
        }
        $aidData = (new AirTradeLogs())::where('order_no', $orderNo)->get(['id'])->first();

        $orderNo = 'AT_' . date('YmdHis') . rand(100000, 999999);

        //更新order 表订单信息
        $order = (new Order())->find($orderData['id']);
        $order->order_no = $orderNo;
        $order->updated_at = $date;
        $order->save();

        //更新air_trade_logs 表订单信息
        $airTradeLogs = (new AirTradeLogs())->find($aidData->id);
        $airTradeLogs->order_no = $orderNo;
        $airTradeLogs->updated_at = $date;
        $airTradeLogs->save();
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
    ];

    public function Trade_Order()
    {
        return $this->belongsTo(TradeOrder::class, 'id','oid');
    }

    public function getUpdatedDateAttribute($value)
    {
//        dd($value);
        return date("Y-m-d H:i:s",strtotime($this->attributes['updated_at']));
    }

}
