<?php

namespace App\Models;

use App\Exceptions\LogicException;
use App\Services\OrderListService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class OrderList extends Model
{
    /**获取用户订单列表
     * @param array $param
     * @return mixed
     * @throws LogicException
     */
    public function userOrderList(array $param)
    {
        //sql 条件
        $where = [
            'o.uid' => $param['uid']
        ];
        //分页
        $data = [
            'page' => $param['page'] ?? 1,
            'perPage' => $param['perPage'] ?? 10,
        ];

        //返回
        return (new OrderList())->getOrders($where, $data);
    }

    /**获取商家订单列表
     * @param array $param
     * @return mixed
     * @throws LogicException
     */
    public function shopOrderList(array $param)
    {
        //sql 条件
        $where = [
            'o.business_uid' => $param['uid']
        ];
        //分页
        $data = [
            'page' => $param['page'] ?? 1,
            'perPage' => $param['perPage'] ?? 10,
        ];

        //返回
        return (new OrderList())->getOrders($where, $data);
    }

    /**获取订单列表
     * @param array $where
     * @param array $data
     * @return mixed
     * @throws \Exception
     */
    public function getOrders(array $where, array $data)
    {
        //判断有无订单
        $this->isUserOrder($where);
        //获取美团、录单、直充订单、话费代充
        $tradeData = json_decode($this->getTradeOrder($where), 1);
        //话费代充、多人代充
        $mobileData = json_decode($this->getMobileOrder($where), 1);
        //视频会员
        $videoData = json_decode($this->getVideoOrder($where), 1);
        //usdt 兑换话费、美团
        $convertData = json_decode($this->getConvertOrder($where), 1);

        //order表开通会员订单
        $orderData = json_decode($this->getOrderKTHY($where), 1);

        //合并订单
        $orderArr = array_merge($tradeData, $mobileData, $videoData, $convertData,$orderData);

        //返回
        return (new OrderListService())->result($orderArr, $data);
    }

    //获取order表开团会员订单
    public function getOrderKTHY($where){
        $await = 10;
        //获取订单列表
        return DB::table('order')->where($where)
            ->get(['id', 'order_no', 'uid', 'price', 'name', 'created_at', 'profit_ratio', 'business_uid',
                'status']);
    }

    /**获取trade_order 订单列表
     * @param array $where
     * @return mixed
     * @throws \Exception
     */
    public function getTradeOrder(array $where)
    {
        //获取订单列表
        return DB::table('order as o')
                ->leftJoin('trade_order as t', 'o.id', 't.oid')
                ->leftJoin('recharge_logs as r', 't.order_no', 'r.order_no')
                ->where($where)
                ->whereIn('o.name', ['美团', '话费', '油卡', '录单'])
                ->get(['o.id', 'o.uid', 'o.price', 'o.name', 'o.created_at', 'o.profit_ratio', 'o.status as ostatus',
                    'o.business_uid', 't.numeric', 't.order_no', 'r.status'])
                ->each(function ($item) {
                    $item->profit_ratio = (int)$item->profit_ratio;
                    switch ($item->name)
                    {
                        case '话费':
                            $item->name = $item->name . '直充';
                            break;
                        /*case '代充':
                            $item->name = '话费' . $item->name;
                            break;*/
                        case '美团':
                        case '录单':
                            $item->status = $item->ostatus == 2 ? 1 : 0;
                            break;
                        case '油卡':
                            break;
                    }
                    $item->numeric = $item->numeric ?? '';
                    $item->status = (new OrderListService())::TRADE_STATUS[$item->status ? 1 : 0];
                    unset($item->ostatus);
                });
    }

    /**获取order_mobile_recharge 订单列表
     * @param array $where
     * @return mixed
     * @throws \Exception
     */
    public function getMobileOrder(array $where)
    {
        $await = 10;
        //获取订单列表
        return DB::table('order as o')
                ->leftJoin('order_mobile_recharge as m', 'o.id', 'm.order_id')
                ->where($where)
                ->whereIn('o.name', ['代充', '批量代充'])
                ->whereIn('m.create_type', [2, 3])
                ->get(['o.id', 'o.order_no', 'o.uid', 'o.price', 'o.name', 'o.created_at', 'o.profit_ratio', 'o.business_uid',
                    'o.status as ostatus', 'm.status', 'm.mobile as numeric'])
                ->each(function ($item) use ($await) {
                    $item->name = '话费' . $item->name;
                    $item->numeric = $item->numeric ?? '';
                    $item->status = $item->ostatus == 2 ? (new OrderListService())::VIDEO_STATUS[$item->status] : (new OrderListService())::VIDEO_STATUS[$await];
                    $item->profit_ratio = (int)$item->profit_ratio;
                });
    }

    /**获取order_video 订单列表
     * @param array $where
     * @return mixed
     * @throws \Exception
     */
    public function getVideoOrder(array $where)
    {
        $await = 10;
        //获取订单列表
        return DB::table('order as o')
                ->leftJoin('order_video as v', 'o.id', 'v.order_id')
                ->where($where)
                ->whereIn('o.name', ['视频会员'])
                ->get(['o.id', 'o.order_no', 'o.uid', 'o.price', 'o.created_at', 'o.profit_ratio', 'o.business_uid',
                    'o.status as ostatus', 'v.status', 'v.create_type as name', 'v.account as numeric'])
                ->each(function ($item) use ($await) {
                    $item->numeric = $item->numeric ?? '';
                    $item->status = $item->ostatus == 2 ? (new OrderListService())::VIDEO_STATUS[$item->status] : (new OrderListService())::VIDEO_STATUS[$await];
                    $item->name = (new OrderListService())::VIDEO_TYPE[$item->name];
                    $item->profit_ratio = (int)$item->profit_ratio;
                });
    }

    /**获取convert_logs 订单列表
     * @param array $where
     * @return mixed
     * @throws \Exception
     */
    public function getConvertOrder(array $where)
    {
        //获取订单列表
        return DB::table('order as o')
                ->leftJoin('convert_logs as c', 'o.id', 'c.oid')
                ->where($where)
                ->whereIn('o.name', ['兑换话费', '兑换额度（美团）'])
                ->get(['o.id', 'o.order_no', 'o.uid', 'o.price', 'o.name', 'o.created_at', 'o.profit_ratio', 'o.business_uid',
                    'c.status', 'c.phone as numeric'])
                ->each(function ($item) {
                    $item->numeric = $item->numeric ?? '';
                    $item->status = (new OrderListService())::CONVERT_STATUS[$item->status ?? 0];
                    $item->profit_ratio = (int)$item->profit_ratio;
                });
    }

    /**获取多人代充充值详情
     * @param string $oid
     * @param array $data
     * @return mixed
     * @throws \Exception
     */
    public function getMobileDetails(string $oid, array $data)
    {
        $await = 10;
        //判断有无详情
        $this->isMobileDetails($oid);

        //返回
        $detailsList = DB::table('order as o')
                ->leftJoin('order_mobile_recharge_details as om', 'o.id', 'om.order_id')
                ->where(['om.order_id' => $oid])
                ->orderBy('om.created_at', 'desc')
                ->forPage($data['page'], $data['perPage'])
                ->get(['o.status as ostatus', 'om.mobile', 'om.money', 'om.status'])
                ->each(function ($item) use ($await) {
                    $item->status = $item->ostatus == 2 ? (new OrderListService())::MOBILEDETAILS_STATUS[$item->status] : (new OrderListService())::MOBILEDETAILS_STATUS[$await];
                    unset($item->ostatus);
                });
        return json_decode($detailsList, 1);
    }

    /**获取多人代充充值详情
     * @param array $param
     * @return mixed
     * @throws LogicException
     */
    public function getMobileDetailsList(array $param)
    {
        //分页
        $data = [
            'page' => $param['page'] ?? 1,
            'perPage' => $param['perPage'] ?? 10,
        ];

        //返回
        return (new OrderList())->getMobileDetails($param['id'], $data);
    }

    /**判断用户订单是否存在
     * @param array $where
     * @return mixed
     * @throws \Exception
     */
    public function isUserOrder(array $where)
    {
        $res = DB::table('order as o')
                ->where($where)
                ->exists();
        if (!$res)
        {
            throw new LogicException('用户订单不存在');
        }
    }

    /**判断订单多人代充详情是否存在
     * @param string $oid
     * @return mixed
     * @throws \Exception
     */
    public function isMobileDetails(string $oid)
    {
        $res = DB::table('order_mobile_recharge_details')
                ->where(['order_id' => $oid])
                ->exists();
        if (!$res)
        {
            throw new LogicException('订单详情不存在');
        }
    }
}
