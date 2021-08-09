<?php

namespace App\Models;

use App\Exceptions\LogicException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Shop
 *
 * @property int $id
 * @property int $uid 消费者UID
 * @property int $business_uid 商家UID
 * @property string $profit_ratio 让利比列(%)
 * @property string $price 消费金额
 * @property string $profit_price 实际让利金额
 * @property int $status 1审核中，2审核通过，3审核失败
 * @property string $name 消费商品名
 * @property string|null $remark 备注
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $state 盟主订单标识,1非盟主订单，2盟主订单
 * @property string|null $pay_status 支付状态：await 待支付；pending 支付处理中； succeeded 支付成功；failed 支付失败,ddyc订单异常
 * @property string|null $to_be_added_integral 用户待加积分
 * @property int|null $to_status 订单处理状态：默认0,1表示待处理,2表示已处理
 * @property int|null $line_up 排队状态,默认0不排队,1表示排队
 * @property string $order_no 斑马充值订单号
 * @method static \Illuminate\Database\Eloquent\Builder|Shop newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Shop newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Shop query()
 * @method static \Illuminate\Database\Eloquent\Builder|Shop whereBusinessUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Shop whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Shop whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Shop whereLineUp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Shop whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Shop whereOrderNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Shop wherePayStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Shop wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Shop whereProfitPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Shop whereProfitRatio($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Shop whereRemark($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Shop whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Shop whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Shop whereToBeAddedIntegral($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Shop whereToStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Shop whereUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Shop whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property int|null $import_day 导入日期
 * @method static \Illuminate\Database\Eloquent\Builder|Shop whereImportDay($value)
 */
class Shop extends Model
{
    use HasFactory;

    protected $table = 'order';

    /**获取商家积分记录
     * @package array $data
     * @return mixed
     * @throws
     */
    public function logsList(array $data)
    {
        $uid = $data['uid'];

        $res = (new User())::where(['id' => $uid, 'status' => 1, 'role' => 2])->exists();
        if (!$res)
        {
            throw new LogicException('该用户不是商家');
        }

        $integralArr = (new User())
            ->join('integral_log', function($join){
                $join->on('users.id', 'integral_log.uid');
            })
            ->where(function($query) use ($uid){
                $query->where(['users.id' => $uid, 'users.status' => 1, 'users.role' => 2, 'integral_log.role' => 2]);
            })
            ->orderBy('integral_log.created_at', 'desc')
            ->forPage($data['page'], $data['pageSize'])
            ->distinct('integral_log.id')
            ->get(['integral_log.id', 'integral_log.operate_type', 'integral_log.amount', 'integral_log.remark', 'integral_log.created_at'])
            ->toArray();
        foreach ($integralArr as $key => $val)
        {
            $integralArr[$key]['created_at'] = date("Y-m-d H:i:s", strtotime($val[ 'created_at' ]));
        }

        return $integralArr;
    }

    /**获取商家排队积分记录
     * @package array $data
     * @return mixed
     * @throws
     */
    public function lineList(array $data)
    {
        $uid = $data['uid'];

        $res = (new User())::where(['id' => $uid, 'status' => 1, 'role' => 2])->exists();
        if (!$res)
        {
            throw new LogicException('该用户不是商家');
        }

        $total = (new User())
            ->join('order', function($join){
                $join->on('users.id', 'order.business_uid');
            })
            ->where(function($query) use ($uid){
                $query->where(['users.id' => $uid, 'users.status' => 1, 'users.role' => 2, 'order.status' => 2, 'order.line_up' => 1]);
            })
            ->sum('order.profit_price');
        $integralList = (new User())
            ->join('order', function($join){
                $join->on('users.id', 'order.business_uid');
            })
            ->where(function($query) use ($uid){
                $query->where(['users.id' => $uid, 'users.status' => 1, 'users.role' => 2, 'order.status' => 2, 'order.line_up' => 1]);
            })
            ->orderBy('order.created_at', 'asc')
            ->forPage($data['page'], $data['pageSize'])
            ->distinct('order.id')
            ->get(['order.id', 'order.name', 'order.profit_price as to_be_added_integral', 'order.created_at'])
            ->toArray();

        //获取排队订单顺序
        $oid = (new Order())::where('line_up', 1)->orderBy('id', 'asc')->get(['id'])->first();
        $newId = $oid->id - 1;
        foreach ($integralList as $key => $val)
        {
            $integralList[$key]['id'] = $val['id'] - $newId;
            $integralList[$key]['created_at'] = date("Y-m-d H:i:s", strtotime($val[ 'created_at' ]));
        }

        return [
            'total'        => sprintf("%.2f",$total),
            'integralList' => $integralList
        ];
    }
}
