<?php

namespace App\Models;

use App\Services\OrderListService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class OrderList extends Model
{
    /**获取订单列表
     * @param array $where
     * @param array $data
     * @return mixed
     * @throws \Exception
     */
    public function getOrders(array $where, array $data)
    {
        //获取美团、录单、直充订单、话费代充
        $tradeData = json_decode($this->getTradeOrder($where), 1);
        //话费代充、多人代充
        $mobileData = json_decode($this->getMobileOrder($where), 1);
        //视频会员
        $videoData = json_decode($this->getVideoOrder($where), 1);
        //usdt 兑换话费、美团
        $convertData = json_decode($this->getConvertOrder($where), 1);

        //合并订单
        $orderArr = array_merge($tradeData, $mobileData, $videoData, $convertData);

        //返回
        return $this->result($orderArr, $data);
    }

    /**返回订单列表
     * @param array $orderArr
     * @param array $data
     * @return mixed
     * @throws \Exception
     */
    public function result(array $orderArr, array $data)
    {
        //订单去重
        $orderList = (new OrderListService())->assocUnique($orderArr, 'id');
        //隐藏显示号码
        foreach ($orderList as &$val)
        {
            if (in_array($val['name'], ['油卡']) && !empty($val['numeric']))
            {
                $val['numeric'] = substr_replace($val['numeric'], '****', -4, -8);
            } elseif (!empty($val['numeric']))
            {
                $val['numeric'] = substr_replace($val['numeric'], '****', 3, 4);
            }
        }

        //按created_at 排序
        array_multisort(array_column($orderList, 'created_at'), SORT_DESC,
            array_column($orderList, 'id'), SORT_DESC, $orderList);

        //数组分页
        $start = ($data['page'] - 1) * $data['perPage'];
        $length = $data['perPage'];
        return array_slice($orderList, $start, $length);
    }

    /**获取trade_order 订单列表
     * @param array $data
     * @return mixed
     * @throws \Exception
     */
    public function getTradeOrder(array $where)
    {
        return DB::table('order as o')
            ->leftJoin('trade_order as t', 'o.id', 't.oid')
            ->leftJoin('recharge_logs as r', 't.order_no', 'r.order_no')
            ->where($where)
            ->whereIn('o.name', ['美团', '话费', '代充', '油卡', '录单'])
            ->get(['o.id', 'o.uid', 'o.price', 'o.name', 'o.created_at', 'o.profit_ratio', 't.numeric', 't.order_no',
                'r.status'])
            ->each(function ($item) {
                switch ($item->name)
                {
                    case '话费':
                        $item->name = $item->name . '直充';
                        $item->ratio = $this->getRatio('set_business_rebate_scale_hf');
                        break;
                    case '代充':
                        $item->name = '话费' . $item->name;
                        $item->ratio = $this->getRatio('set_business_rebate_scale_zl');
                        break;
                    case '美团':
                        $item->status = 1;
                        $item->ratio = $this->getRatio('set_business_rebate_scale_mt');
                        break;
                    case '油卡':
                        $item->ratio = $this->getRatio('set_business_rebate_scale_yk');
                        break;
                }
                $item->numeric = $item->numeric ?? '';
                $item->status = (new OrderListService())::TRADE_STATUS[$item->status ? 1 : 0];
            });
    }

    /**获取order_mobile_recharge 订单列表
     * @param array $data
     * @return mixed
     * @throws \Exception
     */
    public function getMobileOrder(array $where)
    {
        return DB::table('order as o')
            ->leftJoin('order_mobile_recharge as m', 'o.id', 'm.order_id')
            ->where($where)
            ->whereIn('o.name', ['代充', '批量代充'])
            ->whereIn('m.create_type', [2, 3])
            ->get(['o.id', 'o.order_no', 'o.uid', 'o.price', 'o.name', 'o.created_at', 'm.status',
                'm.mobile as numeric'])
            ->each(function ($item) {
                $item->numeric = $item->numeric ?? '';
                $item->status = (new OrderListService())::MOBILE_STATUS[$item->status];
                $item->ratio = $this->getRatio('set_business_rebate_scale_zl');
            });
    }

    /**获取order_video 订单列表
     * @param array $data
     * @return mixed
     * @throws \Exception
     */
    public function getVideoOrder(array $where)
    {
        return DB::table('order as o')
            ->leftJoin('order_video as v', 'o.id', 'v.order_id')
            ->where($where)
            ->whereIn('o.name', ['视频会员'])
            ->get(['o.id', 'o.order_no', 'o.uid', 'o.price', 'o.name', 'o.created_at', 'v.status',
                'v.create_type', 'v.account as numeric'])
            ->each(function ($item) {
                $item->numeric = $item->numeric ?? '';
                $item->status = (new OrderListService())::VIDEO_STATUS[$item->status];
                $item->create_type = (new OrderListService())::VIDEO_TYPE[$item->create_type];
                $item->ratio = $this->getRatio('set_business_rebate_scale_vc');
            });
    }

    /**获取convert_logs 订单列表
     * @param array $data
     * @return mixed
     * @throws \Exception
     */
    public function getConvertOrder(array $where)
    {
        return DB::table('order as o')
            ->leftJoin('convert_logs as c', 'o.id', 'c.oid')
            ->where($where)
            ->whereIn('o.name', ['兑换话费', '兑换额度（美团）'])
            ->get(['o.id', 'o.order_no', 'o.uid', 'o.price', 'o.name', 'o.created_at', 'c.status', 'c.type',
                'c.phone as numeric'])
            ->each(function ($item) {
                $item->numeric = $item->numeric ?? '';
                $item->status = (new OrderListService())::CONVERT_STATUS[$item->status ?? 0];
                $item->ratio = $this->getRatio('set_business_rebate_scale_cl');
            });
    }

    public function getRatio($ratioName)
    {
        $ratio = Setting::getSetting($ratioName);
        return '补贴'. $ratio .'%激励' . $ratio * 5 .'%消费积分';
    }
}
