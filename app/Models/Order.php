<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Exceptions\LogicException;
use Illuminate\Support\Facades\Log;

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

    /**更新状态
     * @param string $orderNo
     * @throws
     */
    public function upOrder(string $orderNo)
    {
        $tradeOrderInfo = DB::table('trade_order')->where('order_no', $orderNo)->first();
        $orders = get_object_vars($tradeOrderInfo);
        DB::table($this->table)->where('id', $orders['oid'])->update(['pay_status' => 'succeeded', 'updated_at' => date("Y-m-d H:i:s")]);
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

    /**更新用户积分
     * @param array $data
     * @throws
     */
    public function upUsers(array $data)
    {
        $tradeOrderInfo = DB::table('trade_order')->where('order_no', $data['order_no'])->first();
        $tradeOrderData = get_object_vars($tradeOrderInfo);

        if (!$tradeOrderData)
            throw new LogicException('订单不存在');

        //话费、美团、油卡直接根据uid 求出原用户的积分，再加上消费积分
        $date = date("Y-m-d H:i:s");
        if (in_array($tradeOrderData['description'], ['HF', 'YK', 'MT']))
        {
            //查询用户积分
            $userInfo = DB::table('users')->where('id', $tradeOrderData['user_id'])->first();
            if (!$userInfo)
                throw new LogicException('用户不存在');
            $usersData = get_object_vars($userInfo);

            //更新用户积分
            $uintegral = $usersData['integral'] + $data['userIntegral'];
            DB::table('users')->where('id', $tradeOrderData['user_id'])->update(['return_integral' => $uintegral, 'updated_at' => $date]);

            //更新来客自营积分
            $shopInfo = DB::table('users')->where('id', 24)->first();
            $shopsData = get_object_vars($shopInfo);
            $sintegral = $shopsData['business_integral'] + $data['shopIntegral'];
            DB::table('users')->where('id', 24)->update(['return_business_integral' => $sintegral]);

            //插入用户积分流水记录
            $integral = $usersData['role'] == 1 ?  $uintegral : $sintegral;
            $data['integral'] = $usersData['role'] == 1 ?  $data['userIntegral'] : $data['shopIntegral'];
            $this->setIntegral($usersData, $integral, $data['integral']);

        } else
        {
            //录入订单
            $orderInfo = DB::table($this->table)->where('id', $tradeOrderData['oid'])->first();
            if (!$orderInfo)
                throw new LogicException('订单不存在');

            $ordersData = get_object_vars($orderInfo);
            //查询用户积分
            $userInfo = DB::table('users')->where('id', $ordersData['uid'])->first();

            if (!$userInfo)
                throw new LogicException('用户不存在');

            $shopInfo = DB::table('users')->where('id', $ordersData['business_uid'])->first();

            if (!$shopInfo)
                throw new LogicException('商家不存在');

            $usersData = get_object_vars($userInfo);
            $shopsData = get_object_vars($shopInfo);
            $uintegral = $usersData['integral'] + $data['userIntegral'];
            $sintegral = $shopsData['business_integral'] + $data['shopIntegral'];

            //更新用户积分
            DB::table('users')->where('id', $ordersData['uid'])->update(['integral' => $uintegral]);

            //更新来客自营积分
            DB::table('users')->where('id', $ordersData['business_uid'])->update(['business_integral' => $sintegral]);

            //插入用户积分流水记录
            $integral = $usersData['role'] == 1 ?  $uintegral : $sintegral;
            $data['integral'] = $usersData['role'] == 1 ?  $data['userIntegral'] : $data['shopIntegral'];
            $this->setIntegral($usersData, $integral, $data['integral']);
        }
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
     * @throws LogicException
     */
    public function setOrder(array $data)
    {
        DB::table($this->table)->insert($data);
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



}
