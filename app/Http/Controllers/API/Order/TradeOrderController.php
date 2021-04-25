<?php

namespace App\Http\Controllers\API\Order;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\TradeOrder;
use App\Exceptions\LogicException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/*
 * 订单信息
 */

class TradeOrderController extends Controller
{

    /**生成订单信息
     * @param array $data
     * @param int $uid
     * @return array
     * @throws
     */
    public function setOrder(array $data, int $uid = 0)
    {
        $time = time();
        if ($uid == 0)
            throw new LogicException('请先登录');
        if (in_array($data['description'], ['HF', 'YK']))
        {
            $data['profitRatio'] = 0.05;
        } elseif ($data['description'] == 'MT')
        {
            $data['profitRatio'] = 0.1;
        } else
        {
            $data['profitRatio'] = 0;
        }

        $orderData = [
            'order_no' => $data['order_no'],
            'user_id' => $uid,
            'title' => $data['title'],
            'price' => $data['price'],
            'numeric' => $data['numeric'],
            'telecom' => $data['telecom'],
            'num' => $data['num'],
            'need_fee' => $data['need_fee'],
            'profit_price' => 0,
            'profit_ratio' => $data['profitRatio'],
            'pay_time' => $time,
            'status' => $data['status'],
            'order_from' => $data['order_from'],
            'integral' => 0,
            'description' => $data['description'],
            'oid' => $data['oid'],
            'created_at' => date("Y-m-d H:i:s"),
            'modified_time' => date("Y-m-d H:i:s")
        ];
        $tradeOrder = new TradeOrder();
        $res = $tradeOrder->setOrder($orderData);
        if ($res)
        {
            return ['code' => 1, 'msg' => '下单成功'];
        } else
        {
            return ['code' => 0, 'msg' => '下单失败'];
        }
    }

    /**获取我的订单
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function getOrderList(Request $request)
    {
        $this->validate($request, [
            'page' => ['bail', 'nullable', 'int', 'min:1'],
            'per_page' => ['bail', 'nullable', 'int', 'min:1', 'max:50'],
        ]);

        $user = $request->user();
        $bOrder = $request->input('bOrder', false);

        //录入订单表
        $orderData = (new Order())
            ->when(!$bOrder,function($query) use ($user) {
                return $query->where('uid', $user->id);
            })
            ->when($bOrder,function($query) use ($user) {
                $query->where('business_uid', $user->id);
            })
            ->orderBy('status', 'asc')
            ->latest('id')
            ->get()->toArray();

        //来客话费、美团、油卡订单表
        $tradeData =
            DB::table('trade_order')
                ->select('user_id', 'price', 'profit_ratio', 'status', 'title', 'created_at')
                ->where('user_id', $user->id)
                ->orderBy('status', 'asc')
                ->get()->toArray();

        if(count($orderData) < 0)
            throw new LogicException('对应订单不存在');

        //获取商家列表
        $businessUid = array_column($orderData, 'business_uid');
        $businessList = DB::table('business_data')
            ->select('uid', 'name')
            ->whereIn('uid', $businessUid)
            ->get()->toArray();
        //获取店铺名字段
        $businessNames = array_column($businessList, 'name', 'uid');

        //组装商铺名字段
        foreach ($orderData as $key => $value)
        {
            $orderData[$key]['business_name'] = $businessNames[$value['business_uid']];
        }

        $tradeOrderData = [];
        //数组对象转化为数组
        foreach ($tradeData as $k => $trade)
        {
            $tradeOrderData[$k] = get_object_vars($trade);
        }

        //来客自营
        foreach ($tradeOrderData as $key => $data)
        {
            $tradeOrderData[$key]['name'] = $data['title'];
            $tradeOrderData[$key]['business_name'] = '来客';
        }

        $orderList = array_merge($orderData, $tradeOrderData);

        //设置分页
        $page = ($request->page - 1) * $request->per_page;
        $data = array_slice($orderList, $page, $request->per_page);

        foreach ($data as $key => $val)
        {
            $data[$key]['phone'] = $user['phone'];
            $data[$key]['username'] = $user['username'];
        }

        return response()->json(['code'=>0, 'msg'=>'获取成功', 'data' => $data]);
    }
}
