<?php

namespace App\Models;

use App\Exceptions\LogicException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
                $join->on('users.id', 'order.uid');
            })
            ->where(function($query) use ($uid){
                $query->where(['users.id' => $uid, 'users.status' => 1, 'users.role' => 2, 'order.status' => 2, 'order.line_up' => 1]);
            })
            ->sum('order.to_be_added_integral');
        $integralList = (new User())
            ->join('order', function($join){
                $join->on('users.id', 'order.uid');
            })
            ->where(function($query) use ($uid){
                $query->where(['users.id' => $uid, 'users.status' => 1, 'users.role' => 2, 'order.status' => 2, 'order.line_up' => 1]);
            })
            ->orderBy('order.created_at', 'desc')
            ->forPage($data['page'], $data['pageSize'])
            ->distinct('order.id')
            ->get(['order.id', 'order.name', 'order.to_be_added_integral', 'order.created_at'])
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
