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
        ];

        $param = [
            'operateType' => ['invite_rebate', 'share_b_rebate'],
            'remark'      => ['邀请商家，获得盈利返佣'],
        ];

        //返回
        return $this->commonAssets($data, $where, $param);
    }

    /**获取用户分享团长数据
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
        ];

        $param = [
            'operateType' => ['invite_rebate', 'share_b_rebate'],
            'remark'      => ['邀请商家，获得盈利返佣', '邀请商家盟主分红', '同级别盟主奖励'],
        ];

        //返回
        return $this->commonAssets($data, $where, $param);
    }

    /**获取团长总积分
    * @param array $data
    * @return mixed
    * @throws
    */
    public function headsIntegral(array $data)
    {
        //组装sql 条件
        $where = [
            'users.id'                 => $data['uid'],
            'users.status'             => 1,
            'users.member_head'        => 2,
        ];

        $operateType = ['share_b_rebate'];
        $remark = ['邀请商家盟主分红', '同级别盟主奖励'];

        //返回
        return $this->teamHeadInt($where, $operateType, $remark);
    }

    /**获取团长团队资产数据
    * @param array $data
    * @return mixed
    * @throws
    */
    public function teamAssets(array $data)
    {
        //组装sql 条件
        $where = [
            'users.id'                 => $data['uid'],
            'users.status'             => 1,
            'users.member_head'        => 2,
        ];

        $param = [
            'operateType' => ['share_b_rebate'],
            'remark'      => ['邀请商家盟主分红', '同级别盟主奖励'],
        ];

        //返回
        return $this->TeamAss($data, $where, $param);
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
    * @param array $param
    * @return mixed
    * @throws
    */
    public function commonAssets(array $data, array $where, array $param)
    {
        //获取资产记录列表
        $assetsData = $this->commonAss($where, $param);
        $assetsList = json_decode($assetsData, 1);

        //获取分享团员订单数据
        $where['order.status'] = 2;
        $orderData = $this->commonOrder($where);
        $orderList = json_decode($orderData, 1);
        
        //总数据
        $integralList = array_merge($assetsList, $orderList);

        //总积分
        unset($where['order.status']);
        $assetsSum = $this->headIntegral($where, $param['operateType'], $param['remark']);

        //数组分页
        $start = ($data['page'] - 1) * $data['perPage'];
        $length = $data['perPage'];
        $integralList = array_slice($integralList, $start, $length);

        //返回
        return [
            'assetsList' => $integralList,
            'assetsSum'  => $assetsSum
        ];
    }

    /**获取用户分享团长团队资产数据
    * @param array $data
    * @param array $where
    * @param array $param
    * @return mixed
    * @throws
    */
    public function TeamAss(array $data, array $where, array $param)
    {
        //获取资产记录列表
        $assetsData = $this->commonAss($where, $param);
        $assetsList = json_decode($assetsData, 1);

        //数组分页
        $start = ($data['page'] - 1) * $data['perPage'];
        $length = $data['perPage'];
        $assetsList = array_slice($assetsList, $start, $length);

        //总积分
        $assetsSum = $this->teamHeadInt($where, $param['operateType'], $param['remark']);

        //返回
        return [
            'assetsList' => $assetsList,
            'assetsSum'  => $assetsSum
        ];
    }

    /**获取用户分享资产数据
    * @param array $where
    * @param array $param
    * @return mixed
    * @throws
    */
    public function commonAss(array $where, array $param)
    {
        //获取分享资产数据
        return DB::table('users')
                    ->leftJoin('assets_logs', 'users.id', 'assets_logs.uid')
                    ->where($where)
                    ->whereIn('assets_logs.operate_type', $param['operateType'])
                    ->whereIn('assets_logs.remark', $param['remark'])
                    ->groupBy('assets_logs.id')
                    ->orderBy('assets_logs.amount', 'desc')
                    ->orderBy('assets_logs.created_at', 'desc')
                    ->get(['assets_logs.amount', 'assets_logs.created_at'])
                    ->each(function ($item) {
                        $item->amount = sprintf('%.2f', $item->amount);
                        $item->name = '消费积分';
                    });
    }

    /**获取用户分享订单数据
    * @param array $data
    * @param array $where
    * @param array $param
    * @return mixed
    * @throws
    */
    public function commonOrder(array $where)
    {
        //获取分享订单数据
        $where['order.status'] = 2;
        return DB::table('users')
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
    }
    

    /**获取用户分享团长总金额
    * @param array $where
    * @param array $operateType
    * @param array $remark
    * @return mixed
    * @throws
    */
    public function teamHeadInt(array $where, array $operateType, array $remark)
    {
        //资产总金额
        $assetsSum = DB::table('users')
                    ->leftJoin('assets_logs', 'users.id', 'assets_logs.uid')
                    ->where($where)
                    ->whereIn('assets_logs.operate_type', $operateType)
                    ->whereIn('assets_logs.remark', $remark)
                    ->sum('assets_logs.amount');

        //返回
        return sprintf('%.2f', $assetsSum);
    }

    /**获取用户分享总让利
    * @param array $where
    * @param array $operateType
    * @param array $remark
    * @return mixed
    * @throws
    */
    public function commonPro(array $where)
    {
        //资产总金额
        $where['order.status'] = 2;
        $profitSum = DB::table('users')
                    ->leftJoin('order', 'users.id', 'order.uid')
                    ->where($where)
                    ->sum('order.profit_price');

        //返回
        return sprintf('%.2f', $profitSum);
    }

    /**获取用户分享团员、团长总金额总让利
    * @param array $where
    * @param array $operateType
    * @param array $remark
    * @return mixed
    * @throws
    */
    public function headIntegral(array $where, array $operateType, array $remark)
    {
        //资产总金额
        $assetsSum = $this->teamHeadInt($where, $operateType, $remark);

        //订单总让利
        $where['order.status'] = 2;
        $profitSum = $this->commonPro($where);

        //返回
        return sprintf('%.2f', $assetsSum + $profitSum);
    }
}
