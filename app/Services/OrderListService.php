<?php

namespace App\Services;

class OrderListService
{
    /**订单去重
     * @param array $arr
     * @param string $key
     * @return mixed
     * @throws
     */
    public function assocUnique(array $arr, string $key)
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

    //trade_order 表状态
    const TRADE_STATUS = [
        0 => '待处理',
        1 => '成功',
    ];

    //order_mobile_recharge 表状态
    const MOBILE_STATUS = [
        0 => '处理中',
        1 => '成功',
        9 => '撤销',
    ];

    //order_video 表状态
    const VIDEO_STATUS = [
        0 => '处理中',
        1 => '成功',
        9 => '撤销',
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
        0 => '待兑换',
        1 => '处理中',
        2 => '成功',
        3 => '失败',
    ];
}
