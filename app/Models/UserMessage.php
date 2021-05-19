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
     * @param int $type
     * @return mixed
     * @throws
     */
    public function setMsg(string $orderNo, int $type)
    {
        $date = date("Y-m-d H:i:s");
        $userInfo = (new Order())
            ->join('trade_order', function($join){
                $join->on('order.id', 'trade_order.oid');
            })
            ->where(['trade_order.order_no' => $orderNo])
            ->distinct('order.id')
            ->get(['order.uid'])
            ->first();
        $message = new UserMessage();
        $message->user_id = $userInfo->uid;
        $message->status = 1;
        $message->type = $type;
        $message->sys_mid = 0;
        $message->created_at = $date;
        $message->updated_at = $date;
        $message->save();
    }

    /**获取消息内容
     * @param int $uid
     * @param int $page
     * @param int $perpage
     * @return mixed
     * @throws
     */
    public function getMsg(int $uid, int $page, int $perpage)
    {
        $res = (new UserMessage())::withTrashed()->where('user_id', $uid)->exists();
        if (!$res)
        {
            throw new LogicException('用户消息不存在');
        }

        //获取充值数据
        $orderData = (new RechargeLogs())
            ->join('trade_order', function($join){
                $join->on('recharge_logs.order_no', 'trade_order.order_no');
            })
            ->where(function($query) use ($uid) {
                $query->where('recharge_logs.status', 1)
                    ->where('trade_order.user_id', $uid)
                    ->where('trade_order.status', 'succeeded');
            })
            ->distinct('recharge_logs.order_no')
            ->get(['recharge_logs.type', 'recharge_logs.created_at', 'trade_order.price', 'trade_order.numeric'])
            ->toArray();
        foreach ($orderData as $key => $value)
        {
            $orderData[$key]['title'] = '';
            $orderData[$key]['content'] = '';
        }

        //录单审核通过
        $userOrder = (new Order())
            ->join('trade_order', function($join){
                $join->on('order.id', 'trade_order.oid');
            })
            ->where(['order.uid' => $uid, 'order.name' => '录单', 'order.status' => 2, 'order.pay_status' => 'succeeded'])
            ->distinct('order.id')
            ->get(['order.created_at', 'order.price', 'trade_order.numeric'])
            ->toArray();
        foreach ($userOrder as $key => $value)
        {
            $userOrder[$key]['type'] = 'LR';
            $userOrder[$key]['title'] = '';
            $userOrder[$key]['content'] = '';
        }

        //合并数据并按创建时间倒序
        $magDatas = array_merge($orderData, $userOrder);

        //系统消息
        $sysMessage = (new UserMessage())
            ->join('sys_message', function($join){
                $join->on('user_message.sys_mid', 'sys_message.id');
            })
            ->where(['user_message.user_id' => $uid])
            ->distinct('sys_message.id')
            ->get(['sys_message.title', 'sys_message.content', 'sys_message.created_at'])
            ->toArray();
        foreach ($sysMessage as $k => $v)
        {
            $sysMessage[$k]['type'] = '';
        }
        $magsArr = array_merge($magDatas, $sysMessage);
        array_multisort(array_column($magsArr, 'created_at'), SORT_DESC, $magsArr);

        $msgList = [];
        $name = '';
        foreach ($magsArr as $key => $val)
        {
            switch ($val['type'])
            {
                case 'HF':
                    $name = '话费';
                    break;
                case 'YK':
                    $name = '油卡';
                    break;
                case 'LR':
                    $name = '录单';
                    break;
            }

            //组装返回数据
            $msgList[] = [
                'title'   => $val['title'] ?: $name . '充值',
                'time'    => $val['created_at'],
                'content' => $val['content'] ?: '本次 '. $val['numeric'] . ' ' . $name . '充值 ' . $val['price'] . ' 元成功',
            ];
        }

        //分页
        $start = ($page - 1) * $perpage;
        $length = $perpage;

        //返回
        return array_slice($msgList, $start, $length);
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
