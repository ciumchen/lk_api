<?php

namespace App\Models;

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

    /**更新状态
     * @param string $orderNo
     * @throws
     */
    public function upOrder(string $orderNo)
    {
        $tradeOrderInfo = DB::table('trade_order')->where('order_no', $orderNo)->first();
        $orders = get_object_vars($tradeOrderInfo);
        $data = [
            'pay_status' => 'succeeded',
            'updated_at' => date("Y-m-d H:i:s")
        ];

        /*if ($orders['description'] == 'LR')
        {
            $data['status'] = 2;
        }*/
        //更新 order 订单表
        DB::table($this->table)->where('id', $orders['oid'])->update($data);
        /*if ($orders['description'] == 'LR')
        {
            $res = DB::table($this->table)->where('id', $orders['oid'])->first();
            $resData = get_object_vars($res);
            $this->getPast($resData['status'], $orders['oid']);
        }*/
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
