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
    /**机票数据检查
    * @param array $data
    * @return mixed
    * @throws
    */
    public function userShare(array $data)
    {
        //获取团员数据
        $lowerData = DB::table('users')
                    ->select(DB::raw('users.id, users.avatar, users.phone, cast(sum(order.price) as decimal(10,2)) as totalPrice, cast(sum(assets_logs.amount)as decimal(10,2)) as totalAssets'))
                    ->leftJoin('order', 'users.id', '=', 'order.uid')
                    ->leftJoin('assets_logs', 'users.id', '=', 'assets_logs.uid')
                    ->where(['users.invite_uid' => $data['uid'], 'users.status' => 1, 'users.role' => 1])
                    ->forPage($data['page'], $data['perPage'])
                    ->groupBy('users.id')
                    ->get();
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

        //返回
        return [
            'lowerList' => $lowerList,
            'countList' => $countList,
        ];
    }
}