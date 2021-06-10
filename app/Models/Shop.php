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

        $res = (new User())::where(['id' => $uid, 'status' => 1])->exists();
        if (!$res)
        {
            throw new LogicException('该用户信息不存在');
        }

        return (new User())
            ->join('integral_log', function($join){
                $join->on('users.id', 'integral_log.uid');
            })
            ->where(function($query) use ($uid){
                $query->where(['users.id' => $uid, 'users.status' => 1, 'users.role' => 2]);
            })
            ->orderBy('integral_log.created_at', 'desc')
            ->forPage($data['page'], $data['pageSize'])
            ->distinct('users.id')
            ->get(['integral_log.operate_type', 'integral_log.amount', 'integral_log.remark', 'integral_log.created_at'])
            ->toArray();
    }

    /**获取商家排队积分记录
     * @package array $data
     * @return mixed
     * @throws
     */
    public function lineList(array $data)
    {
        $uid = $data['uid'];

        $res = (new User())::where(['id' => $uid, 'status' => 1])->exists();
        if (!$res)
        {
            throw new LogicException('该用户信息不存在');
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
            ->distinct('users.id')
            ->get(['order.name', 'order.to_be_added_integral', 'order.created_at'])
            ->toArray();
        return [
            'total'        => $total,
            'integralList' => $integralList
        ];
    }
}
