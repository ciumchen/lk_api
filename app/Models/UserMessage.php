<?php

namespace App\Models;

use App\Exceptions\LogicException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use App\Models\TradeOrder;

class UserMessage extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'user_message';

    /**插入消息
     * @param string $orderNo
     * @return mixed
     * @throws
     */
    public function setMsg(string $orderNo)
    {
        $date = date("Y-m-d H:i:s");
        $userInfo = (new TradeOrder())->tradeOrderInfo($orderNo);
        $message = new UserMessage();
        $message->user_id = $userInfo->user_id;
        $message->status = 1;
        $message->type = 1;
        $message->created_at = $date;
        $message->updated_at = $date;
        $message->save();
    }

    /**获取消息内容
     * @param int $uid
     * @return mixed
     * @throws
     */
    public function getMsg(int $uid)
    {
        $res = (new UserMessage())::withTrashed()->where('user_id', $uid)->exists();
        if (!$res)
        {
            throw new LogicException('用户消息不存在');
        }

        //获取充值数据
        $msgArr = (new RechargeLogs())
            ->join('trade_order', function($join){
                $join->on('recharge_logs.order_no', 'trade_order.order_no');
            })
            ->where(function($query) use ($uid) {
                $query->where('recharge_logs.status', 1)
                    ->where('trade_order.user_id', $uid)
                    ->where('trade_order.status', 'succeeded');
            })
            ->orderBy('recharge_logs.created_at', 'desc')
            ->distinct('recharge_logs.order_no')
            ->get(['recharge_logs.*', 'trade_order.price', 'trade_order.numeric'])
            ->toArray();

        $msgList = [];
        $name = '';
        foreach ($msgArr as $key => $val)
        {
            switch ($val['type'])
            {
                case 'HF':
                    $name = '话费';
                    break;
                case 'YK':
                    $name = '油卡';
                    break;
            }

            //组装返回数据
            $msgList[] = [
                'title'   => $name . '充值',
                'time'    => $val['created_at'],
                'content' => '本次 '. $val['numeric'] . ' ' . $name . '充值 ' . $val['price'] . ' 元成功',
            ];
        }

        //返回
        return $msgList;
    }

    /**获取消息小红点
     * @param int $uid
     * @return mixed
     * @throws
     */
    public function getReddot(int $uid)
    {
        $res = (new UserMessage())::where('user_id', $uid)->exists();
        if (!$res)
        {
            throw new LogicException('用户消息不存在');
        }

        return (new UserMessage())::where(['user_id' => $uid, 'deleted_at' => null])->exists();
    }

    /**删除消息小红点
     * @param int $uid
     * @throws
     */
    public function delReddot(int $uid)
    {
        $res = (new UserMessage())::where('user_id', $uid)->exists();
        if (!$res)
        {
            throw new LogicException('用户消息不存在');
        }

        (new UserMessage())
            ->where(function($query) use ($uid){
            $query->where('user_id', $uid)
                ->where('deleted_at', null);
        })
        ->delete();
    }
}
