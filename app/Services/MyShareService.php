<?php

namespace App\Services;

use App\Exceptions\LogicException;
use App\Http\Controllers\API\Airticket\OrderPayBillController;
use App\Models\AirTradeLogs;
use App\Models\Order;
use App\Models\OrderAirTrade;
use App\Models\RechargeLogs;
use App\Models\User;
use DB;

class MyShareService
{
    /**获取用户分享团员数据
    * @param array $data
    * @return mixed
    * @throws
    */
    public function userShare(array $data)
    {
        //组装sql 条件
        $where = [
            'users.invite_uid'         => $data['uid'],
            'users.status'             => 1,
            'users.role'               => 1,
            'order.status'             => 2,
            'assets_logs.operate_type' => 'invite_rebate'
        ];

        //返回
        return $this->commonShare($data, $where);
    }

    /**获取用户分享商家数据
    * @param array $data
    * @return mixed
    * @throws
    */
    public function shopShare(array $data)
    {
        //组装sql 条件
        $where = [
            'users.invite_uid'         => $data['uid'],
            'users.status'             => 1,
            'users.role'               => 2,
            'order.status'             => 2,
            'assets_logs.operate_type' => 'share_b_rebate',
            'assets_logs.remark' => '邀请商家，获得盈利返佣'
        ];

        //返回
        return $this->commonShare($data, $where);        
    }

    /**获取用户分享团员数据
    * @param array $data
    * @return mixed
    * @throws
    */
    public function usersAssets(array $data)
    {
        //组装sql 条件
        $where = [
            'users.invite_uid'         => $data['uid'],
            'users.status'             => 1,
            'assets_logs.operate_type' => 'invite_rebate',
            'assets_logs.operate_type' => 'share_b_rebate',
            'assets_logs.remark'       => '邀请商家，获得盈利返佣',
        ];

        //返回
        return $this->commonAssets($data, $where);
    }

    /**获取用户分享团员、商家数据
    * @param array $data
    * @param array $where
    * @return mixed
    * @throws
    */
    public function commonShare(array $data, array $where)
    {
        //获取分享团员数据
        $lowerData = DB::table('users')
                    ->select(DB::raw('users.id, users.avatar, users.phone, users.member_head, cast(sum(order.price) as decimal(10,2)) as totalPrice, cast(sum(assets_logs.amount)as decimal(10,2)) as totalAssets'))
                    ->leftJoin('order', 'users.id', 'order.uid')
                    ->leftJoin('assets_logs', 'users.id', 'assets_logs.uid')
                    ->where($where)
                    ->forPage($data['page'], $data['perPage'])
                    ->groupBy('users.id')
                    ->get()
                    ->each(function ($item) {
                        $item->phone = substr_replace($item->phone,'****',3,4);
                    });
        $lowerList = json_decode($lowerData, 1);
        
        //根据累计奖励排序
        array_multisort(array_column($lowerList, 'totalAssets'), SORT_DESC, $lowerList);

        //根据不同角色获取数量
        $userCount = DB::table('users')
                    ->select(DB::raw("if(role = 1, 'userCount', 'shopCount') type, count(*) total"))
                    ->where(['invite_uid' => $data['uid'], 'status' => 1])
                    ->groupBy('role')
                    ->get();
        $countList = (json_decode($userCount, 1));

        //获取消费奖励和让利奖励
        $totalData = DB::table('users')
                    ->select(DB::raw('cast(sum(order.profit_price) as decimal(10,2)) as totalProfit, cast(sum(assets_logs.amount)as decimal(10,2)) as totalAssets'))
                    ->leftJoin('order', 'users.id', 'order.uid')
                    ->leftJoin('assets_logs', 'users.id', 'assets_logs.uid')
                    ->where($where)
                    ->get();
        $totalList = (json_decode($totalData, 1));

        //返回
        return [
            'lowerList' => $lowerList,
            'countList' => $countList,
            'totalList' => $totalList,
            'rewardSum' => sprintf('%.2f', $totalList[0]['totalProfit'] + $totalList[0]['totalAssets']),
        ];
    }

    /**获取用户分享团员、商家资产数据
    * @param array $data
    * @param array $where
    * @return mixed
    * @throws
    */
    public function commonAssets(array $data, array $where)
    {
        //获取分享团员数据
        $assetsData = DB::table('users')
                    ->leftJoin('assets_logs', 'users.id', 'assets_logs.uid')
                    ->where($where)
                    ->forPage($data['page'], $data['perPage'])
                    ->groupBy('assets_logs.id')
                    ->get(['assets_logs.amount', 'assets_logs.created_at'])
                    ->each(function ($item) {
                        $item->amount = sprintf('%.2f', $item->amount);
                        $item->name = '分享积分';
                    });
        $assetsList = json_decode($assetsData, 1);

        $assetsSum = DB::table('users')
                    ->leftJoin('assets_logs', 'users.id', 'assets_logs.uid')
                    ->where($where)
                    ->sum('assets_logs.amount');

        //返回
        return [
            'assetsList' => $assetsList,
            'assetsSum'  => sprintf('%.2f', $assetsSum)
        ];
    }
}