<?php

namespace App\Models;

use App\Exceptions\LogicException;
use App\Libs\Yuntong\Request;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use App\Models\TradeOrder;

/**
 * App\Models\UserMessage
 *
 * @property int $id
 * @property int|null $user_id users表 -- id
 * @property int|null $type 消息类型：1 充值；2 录单；8 系统
 * @property int $status 消息状态：1 成功；0 失败
 * @property int $sys_mid sys_message表 -- id
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @property \Illuminate\Support\Carbon|null $deleted_at 删除时间
 * @property int $is_del 是否删除：0 否；1 是
 * @property string|null $order_no trade_order表 -- order_no
 * @method static \Illuminate\Database\Eloquent\Builder|UserMessage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserMessage newQuery()
 * @method static \Illuminate\Database\Query\Builder|UserMessage onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|UserMessage query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserMessage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserMessage whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserMessage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserMessage whereIsDel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserMessage whereOrderNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserMessage whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserMessage whereSysMid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserMessage whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserMessage whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserMessage whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|UserMessage withTrashed()
 * @method static \Illuminate\Database\Query\Builder|UserMessage withoutTrashed()
 * @mixin \Eloquent
 */
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
        $message->is_del = 0;
        $message->order_no = $orderNo;
        $message->created_at = $date;
        $message->updated_at = $date;
        $message->save();
    }

    /**插入机票消息
     * @param string $orderNo
     * @param int $type
     * @return mixed
     * @throws
     */
    public function setAirMsg(string $orderNo, int $type)
    {
        $date = date("Y-m-d H:i:s");
        $userInfo = (new Order())->getOrderInfo($orderNo)->first();

        $message = new UserMessage();
        $message->user_id = $userInfo->uid;
        $message->status = 1;
        $message->type = $type;
        $message->sys_mid = 0;
        $message->is_del = 0;
        $message->order_no = $orderNo;
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
        $res = (new UserMessage())::withTrashed()->where(['user_id' =>  $uid, 'is_del' => 0])->exists();
        if (!$res)
        {
            throw new LogicException('用户消息不存在');
        }

        //获取充值数据
        $tradeArr = (new RechargeLogs())
            ->join('trade_order', function($join){
                $join->on('recharge_logs.order_no', 'trade_order.order_no');
            })
            ->join('user_message', function($join){
                $join->on('trade_order.order_no', 'user_message.order_no');
            })
            ->where(function($query) use ($uid) {
                $query->where('recharge_logs.status', 1)
                    ->where('trade_order.user_id', $uid)
                    ->where('trade_order.status', 'succeeded')
                    ->where('user_message.is_del', 0);
            })
            ->distinct('recharge_logs.order_no')
            ->get(['user_message.id', 'recharge_logs.type', 'recharge_logs.created_at', 'trade_order.price', 'trade_order.numeric'])
            ->toArray();

        //机票订单
        $orderArr = (new RechargeLogs())
            ->join('order', function($join){
                $join->on('recharge_logs.order_no', 'order.order_no');
            })
            ->join('user_message', function($join){
                $join->on('order.order_no', 'user_message.order_no');
            })
            ->where(function($query) use ($uid) {
                $query->where('recharge_logs.status', 1)
                    ->where('order.uid', $uid)
                    ->where('order.status', 2)
                    ->where('user_message.is_del', 0);
            })
            ->distinct('recharge_logs.order_no')
            ->get(['user_message.id', 'recharge_logs.type', 'recharge_logs.created_at', 'order.price'])
            ->toArray();
        $orderData = array_merge($tradeArr, $orderArr);
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
            ->join('user_message', function($join){
                $join->on('trade_order.order_no', 'user_message.order_no');
            })
            ->where(['order.uid' => $uid, 'order.name' => '录单', 'order.status' => 2, 'order.pay_status' => 'succeeded', 'user_message.is_del' => 0])
            ->distinct('order.id')
            ->get(['user_message.id', 'order.created_at', 'order.price', 'trade_order.numeric'])
            ->toArray();
        foreach ($userOrder as $key => $value)
        {
            $userOrder[$key]['type'] = 'LR';
            $userOrder[$key]['title'] = '';
            $userOrder[$key]['content'] = '';
        }

        $magDatas = array_merge($orderData, $userOrder);

        //美团、滴滴等自营消息
        $selfMessage = (new UserMessage())
            ->join('sys_message', function($join){
                $join->on('user_message.sys_mid', 'sys_message.id');
            })
            ->withTrashed()
            ->where(['user_message.user_id' => $uid, 'user_message.type' => 3, 'is_del' => 0])
            ->distinct('sys_message.id')
            ->get(['user_message.id', 'sys_message.title', 'sys_message.content', 'sys_message.created_at'])
            ->toArray();
        foreach ($selfMessage as $k => $v)
        {
            $selfMessage[$k]['type'] = '';
        }
        $magArr = array_merge($magDatas, $selfMessage);

        //按创建时间排序
        array_multisort(array_column($magArr, 'created_at'), SORT_DESC, $magArr);
        $key = 'id';
        $magArr = $this->assoc_unique($magArr , $key);

        $msgList = [];
        $name = '';
        foreach ($magArr as $key => $val)
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
                case 'AT':
                    $name = '飞机票';
                    break;
            }

            //组装返回数据
            if (in_array($name, ['话费', '油卡', '录单']))
            {
                $msgList[] = [
                    'id'      => $val['id'],
                    'title'   => $val['title'] ?: $name . '充值',
                    'time'    => $val['created_at'],
                    'content' => $val['content'] ?: '本次 '. $val['numeric'] . ' ' . $name . '充值 ' . $val['price'] . ' 元成功',
                ];
            } else
            {
                $notes = $name == '飞机票' ? '订单已完成' : '';
                $msgList[] = [
                    'id'      => $val['id'],
                    'title'   => $val['title'] ?: $name . '购买',
                    'time'    => $val['created_at'],
                    'content' => $val['content'] ?: '本次购买' . $name . ' ' . $val['price'] . ' 元' . $notes,
                ];
            }

        }

        //分页
        $start = ($page - 1) * $perpage;
        $length = $perpage;

        //返回
        return array_slice($msgList, $start, $length);
    }

    /**获取系统消息内容
     * @param int $uid
     * @param int $page
     * @param int $perpage
     * @return mixed
     * @throws
     */
    public function getSysMsg(int $uid, int $page, int $perpage)
    {
        $res = (new UserMessage())::withTrashed()->where(['user_id' => $uid, 'is_del' => 0])->exists();
        if (!$res)
        {
            throw new LogicException('用户消息不存在');
        }

        //系统消息
        $sysMessage = (new UserMessage())
            ->join('sys_message', function($join){
                $join->on('user_message.sys_mid', 'sys_message.id');
            })
            ->withTrashed()
            ->where(['user_message.user_id' => $uid, 'user_message.type' => 8, 'is_del' => 0])
            ->distinct('sys_message.id')
            ->get(['user_message.id', 'sys_message.title', 'sys_message.content', 'sys_message.created_at'])
            ->toArray();

        //合并数据并按创建时间倒序
        array_multisort(array_column($sysMessage, 'created_at'), SORT_DESC, $sysMessage);
        $key = 'id';
        $sysMessage = $this->assoc_unique($sysMessage , $key);

        $msgList = [];
        foreach ($sysMessage as $key => $val)
        {
            //组装返回数据
            $msgList[] = [
                'id'      => $val['id'],
                'title'   => $val['title'],
                'time'    => $val['created_at'],
                'content' => $val['content'],
            ];
        }

        //分页
        $start = ($page - 1) * $perpage;
        $length = $perpage;

        //返回
        $msgRes = array_slice($msgList, $start, $length);
        if (!$msgRes)
        {
            return json_encode(['code' => 10000, 'msg' => '暂无系统消息']);
        }
        return $msgRes;
    }

    /**获取消息小红点
     * @param int $uid
     * @return mixed
     * @throws
     */
    public function getReddot(int $uid)
    {
        return (new UserMessage())::where(['user_id' => $uid, 'deleted_at' => null, 'is_del' => 0])->exists();
    }

    /**删除消息小红点
     * @param int $uid
     * @throws
     */
    public function delReddot(int $uid)
    {
        (new UserMessage())
            ->where(function($query) use ($uid){
            $query->where('user_id', $uid)
                ->where('deleted_at', null)
                ->whereIn('type', [1, 2, 3]);
        })
        ->delete();
    }

    /**获取系统消息小红点
     * @param int $uid
     * @return mixed
     * @throws
     */
    public function getSysReddot(int $uid)
    {
        return (new UserMessage())::where(['user_id' => $uid, 'type' => 8, 'deleted_at' => null, 'is_del' => 0])->exists();
    }

    /**删除系统消息小红点
     * @param int $uid
     * @throws
     */
    public function delSysReddot(int $uid)
    {
        (new UserMessage())
            ->where(function($query) use ($uid){
                $query->where('user_id', $uid)
                    ->where('type', 8)
                    ->where('deleted_at', null);
            })
            ->delete();
    }

    /**删除单条消息
     * @param int $id
     * @return mixed
     * @throws
     */
    public function delMsg(int $id)
    {
        $res = (new UserMessage())::withTrashed()->where(['id' => $id, 'is_del' => 0])->exists();
        if (!$res)
        {
            throw new LogicException('用户消息不存在');
        }

        $userMessage = (new UserMessage())::withTrashed()->find($id);
        $userMessage->is_del = 1;
        $userMessage->updated_at = date("Y-m-d H:i:s");
        $data = $userMessage->save();
        if ($data)
        {
            return json_encode(['code' => 1, 'msg' => '删除消息成功']);
        } else
        {
            return json_encode(['code' => 0, 'msg' => '删除消息失败']);
        }
    }

    /**删除所有消息
     * @param int $uid
     * @return mixed
     * @throws
     */
    public function delAllMsg(int $uid)
    {
        $res = (new UserMessage())::withTrashed()->where(['user_id' => $uid, 'is_del' => 0])->exists();
        if (!$res)
        {
            throw new LogicException('用户消息不存在');
        }

        $msgData = [
            'is_del' => 1,
            'updated_at' => date("Y-m-d H:i:s")
        ];
        $data = (new UserMessage())::withTrashed()->where('user_id', $uid)->update($msgData);

        if ($data)
        {
            return json_encode(['code' => 1, 'msg' => '删除消息成功']);
        } else
        {
            return json_encode(['code' => 0, 'msg' => '删除消息失败']);
        }
    }

    /**删除所有消息
     * @param array $arr
     * @param string $key
     * @return mixed
     * @throws
     */
    public function assoc_unique(array $arr, string $key)
    {
        $tmp_arr = array();
        foreach ($arr as $k => $v)
        {
            if (in_array($v[$key], $tmp_arr))
            {
                //搜索$v[$key]是否在$tmp_arr数组中存在，若存在返回true
                unset($arr[$k]);
            } else
            {
                $tmp_arr[] = $v[$key];
            }
        }

        return $arr;
    }
}
