<?php

namespace App\Services;

use App\Models\BusinessData;
use App\Models\Setting;

class OrderListService
{
    /**返回订单列表
     * @param array $orderArr
     * @param array $data
     * @return mixed
     * @throws \Exception
     */
    public function result(array $orderArr, array $data)
    {
        //订单去重
        $orderList = $this->assocUnique($orderArr, 'id');

        //获取商家信息
        $businessList = json_decode((new BusinessData())->getBusiness(), 1);
        $businessArr = array_column($businessList, null, 'uid');

        //隐藏显示号码
        foreach ($orderList as &$val)
        {
            if (in_array($val['name'], ['油卡']) && !empty($val['numeric']))
            {
                $val['numeric'] = substr_replace($val['numeric'], '****', 11, 4);
            } elseif (!empty($val['numeric']))
            {
                $val['numeric'] = substr_replace($val['numeric'], '****', 3, 4);
            }

            $val['business_name'] = $businessArr[$val['business_uid']]['name'] ?? '商家已下架';
        }

        //按created_at 排序
        array_multisort(array_column($orderList, 'created_at'), SORT_DESC,
            array_column($orderList, 'id'), SORT_DESC, $orderList);

        //数组分页
        $start = ($data['page'] - 1) * $data['perPage'];
        $length = $data['perPage'];
        return array_slice($orderList, $start, $length);
    }

    /**订单去重
     * @param array $arr
     * @param string $key
     * @return mixed
     * @throws
     */
    public function assocUnique(array $arr, string $key)
    {
        $tmpArr = array();
        foreach ($arr as $k => $v)
        {
            if (in_array($v[$key], $tmpArr))
            {
                //搜索$v[$key]是否在$tmp_arr数组中存在，若存在返回true
                unset($arr[$k]);
            } else
            {
                $tmpArr[] = $v[$key];
            }
        }
        return $arr;
    }

    /**获取让利比例
     * @return mixed
     * @throws \Exception
     */
    /*public function getTradeRatio()
    {
        //话费直充让利比例
        $hfratio = Setting::getSetting('set_business_rebate_scale_hf');
        //话费代充让利比例
        $zlratio = Setting::getSetting('set_business_rebate_scale_zl');
        //美团让利比例
        $mtratio = Setting::getSetting('set_business_rebate_scale_mt');
        //油卡让利比例
        $ykratio = Setting::getSetting('set_business_rebate_scale_yk');
        return [
            'hfratio' => '补贴'. $hfratio .'%激励' . $hfratio * 5 .'%消费积分',
            'zlratio' => '补贴'. $zlratio .'%激励' . $zlratio * 5 .'%消费积分',
            'mtratio' => '补贴'. $mtratio .'%激励' . $mtratio * 5 .'%消费积分',
            'ykratio' => '补贴'. $ykratio .'%激励' . $ykratio * 5 .'%消费积分'
        ];
    }*/

    /**获取让利比例
     * @param string $ratioName
     * @return mixed
     * @throws \Exception
     */
    /*public function getRatio(string $ratioName)
    {
        //让利比例
        $ratio = Setting::getSetting($ratioName);
        return '补贴'. $ratio .'%激励' . $ratio * 5 .'%消费积分';
    }*/

    //trade_order 表状态
    const TRADE_STATUS = [
        0 => '待处理',
        1 => '成功',
    ];

    //order_mobile_recharge 表状态
    const MOBILE_STATUS = [
        0 => '充值中',
        1 => '成功',
        9 => '撤销',
        10 => '待处理'
    ];

    //order_video 表状态
    const VIDEO_STATUS = [
        0 => '充值中',
        1 => '成功',
        9 => '撤销',
        10 => '待处理'
    ];

    //order_video 表充值类型
    const VIDEO_TYPE = [
        1  => '优酷会员',
        2  => '迅雷会员',
        3  => '土豆会员',
        4  => '爱奇艺会员',
        5  => '乐视会员',
        6  => '好莱坞会员',
        7  => '芒果TV移动PC端会员',
        8  => '芒果TV全屏会员',
        9  => '搜狐会员',
        10 => '腾讯会员'
    ];

    //convert_logs 表状态
    const CONVERT_STATUS = [
        0 => '待处理',
        1 => '充值中',
        2 => '成功',
        3 => '失败',
    ];

    //order_mobile_recharge_details 表状态
    const MOBILEDETAILS_STATUS = [
        0 => '充值中',
        1 => '成功',
        9 => '撤销',
        10 => '待处理'
    ];
}
