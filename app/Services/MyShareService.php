<?php

namespace App\Services;
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
            'assets_logs.remark'       => '邀请商家，获得盈利返佣'
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
            'users.id'                 => $data['uid'],
            'users.status'             => 1,
            'users.member_head'        => 1,
            'assets_logs.operate_type' => ['invite_rebate', 'share_b_rebate'],
            'assets_logs.remark'       => ['邀请商家，获得盈利返佣'],
        ];

        //返回
        return $this->commonAssets($data, $where);
    }

    /**获取用户分享团员数据
    * @param array $data
    * @return mixed
    * @throws
    */
    public function headsAssets(array $data)
    {
        //组装sql 条件
        $where = [
            'users.id'                 => $data['uid'],
            'users.status'             => 1,
            'users.member_head'        => 2,
            'assets_logs.operate_type' => ['invite_rebate', 'share_b_rebate'],
            'assets_logs.remark'       => ['邀请商家，获得盈利返佣', '邀请商家盟主分红', '同级别盟主奖励'],
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
        //商家
        $role = $where['users.role'] == 2 ? true : false;

        //获取分享团员数据
        $userList = DB::table('users')
                    ->where(['invite_uid' => $data['uid'], 'role' => $where['users.role'], 'status' => $where['users.status']])
                    ->get(['id', 'avatar', 'phone', 'member_head']);
        $userArr = json_decode($userList, 1);
        $uids = array_column($userArr, 'id');

        //消费总金额
        $orderPrice = DB::table('order')
                        ->select(DB::raw('uid, cast(sum(price) as decimal(10,2)) as totalPrice'))
                        ->where(['status' => $where['order.status']])
                        ->whereIn('uid', $uids)
                        ->groupBy('uid')
                        ->get();
        $sumPrice = array_column(json_decode($orderPrice, 1), null, 'uid');
        
        //消费总奖励
        $orderPrice = DB::table('assets_logs')
                        ->select(DB::raw('uid, cast(sum(amount)as decimal(10,2)) as totalAssets'))
                        ->where(['operate_type' => $where['assets_logs.operate_type']])
                        ->whereIn('uid', $uids)
                        ->when($role, function ($query) use($where) {
                            return $query->where('remark', $where['assets_logs.remark']);
                        })
                        ->groupBy('uid')
                        ->get();
        $sumAssets = array_column(json_decode($orderPrice, 1), null, 'uid');

        //组装数据
        foreach ($userArr as $key => $val)
        {
            $userArr[$key]['phone'] = substr_replace($val['phone'],'****',3,4);
            $userArr[$key]['totalPrice'] = sprintf('%.2f', $sumPrice[$val['id']]['totalPrice'] ?? 0);
            $userArr[$key]['totalAssets'] = sprintf('%.2f', $sumAssets[$val['id']]['totalAssets'] ?? 0);
        }

        //根据总奖励排序
        array_multisort(array_column($userArr, 'totalAssets'), SORT_DESC, $userArr);

        //根据不同角色获取数量
        $userCount = DB::table('users')
                    ->select(DB::raw("if(role = 1, 'userCount', 'shopCount') type, count(*) total"))
                    ->where(['invite_uid' => $data['uid'], 'status' => 1])
                    ->groupBy('role')
                    ->get();
        $countList = (json_decode($userCount, 1));

        //获取消费总让利奖励
        $totalProfit = DB::table('order')
                        ->where(['status' => $where['order.status']])
                        ->whereIn('uid', $uids)
                        ->groupBy('uid')
                        ->sum('profit_price');

        //获取消费总奖励
        $totalAssets = DB::table('assets_logs')
                        ->where(['operate_type' => $where['assets_logs.operate_type']])
                        ->whereIn('uid', $uids)
                        ->when($role, function ($query) use($where) {
                            $query->where(['remark' => $where['assets_logs.remark']]);
                        })
                        ->sum('amount');
        
        $totalList = [
            'totalProfit' => sprintf('%.2f', $totalProfit),
            'totalAssets' => sprintf('%.2f', $totalAssets),
        ];

        //返回
        return [
            'userArr' => $userArr,
            'countList' => $countList,
            'totalList' => $totalList,
            'rewardSum' => sprintf('%.2f', $totalProfit + $totalAssets),
        ];
    }

    /**获取用户分享团员、团长资产数据
    * @param array $data
    * @param array $where
    * @return mixed
    * @throws
    */
    public function commonAssets(array $data, array $where)
    {
        //类型
        $operateType = $where['assets_logs.operate_type'];
        unset($where['assets_logs.operate_type']);

        //团长
        $memberHead = $where['users.member_head'] == 2 ? true : false;
        //备注
        $remark = $where['assets_logs.remark'];
        if ($memberHead)
        {
            unset($where['assets_logs.remark']);
        }
        
        //获取分享团员资产数据
        $assetsData = DB::table('users')
                    ->leftJoin('assets_logs', 'users.id', 'assets_logs.uid')
                    ->where($where)
                    ->whereIn('assets_logs.operate_type', $operateType)
                    ->when($memberHead, function ($query) use($remark) {
                        return $query->whereIn('assets_logs.remark', $remark);
                    })
                    ->groupBy('assets_logs.id')
                    ->orderBy('assets_logs.amount', 'desc')
                    ->orderBy('assets_logs.created_at', 'desc')
                    ->get(['assets_logs.amount', 'assets_logs.created_at'])
                    ->each(function ($item) {
                        $item->amount = sprintf('%.2f', $item->amount);
                        $item->name = '消费积分';
                    });
        $assetsList = json_decode($assetsData, 1);

        //获取分享团员订单数据
        unset($where['assets_logs.remark']);
        $orderData = DB::table('users')
                    ->leftJoin('order', 'users.id', 'order.uid')
                    ->where($where)
                    ->groupBy('order.id')
                    ->orderBy('order.profit_price', 'desc')
                    ->orderBy('order.created_at', 'desc')
                    ->get(['order.profit_price', 'order.created_at'])
                    ->each(function ($item) {
                        $item->profit_price = sprintf('%.2f', $item->profit_price);
                        $item->name = '让利积分';
                    });
        $orderList = json_decode($orderData, 1);
        $integralList = array_merge($assetsList, $orderList);

        //资产总金额
        $assetsSum = DB::table('users')
                    ->leftJoin('assets_logs', 'users.id', 'assets_logs.uid')
                    ->where($where)
                    ->whereIn('assets_logs.operate_type', $operateType)
                    ->when($memberHead, function ($query) use($remark) {
                        return $query->whereIn('assets_logs.remark', $remark);
                    })
                    ->sum('assets_logs.amount');

        //订单总让利
        unset($where['assets_logs.remark']);
        $profitSum = DB::table('users')
                    ->leftJoin('order', 'users.id', 'order.uid')
                    ->where($where)
                    ->sum('order.profit_price');

        //数组分页
        $start = ($data['page'] - 1) * $data['perPage'];
        $length = $data['perPage'];
        $integralList = array_slice($integralList, $start, $length);

        //返回
        return [
            'assetsList' => $integralList,
            'assetsSum'  => sprintf('%.2f', $assetsSum + $profitSum)
        ];
    }
}
