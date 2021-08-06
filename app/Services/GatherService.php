<?php

namespace App\Services;

use App\Exceptions\LogicException;
use App\Models\Gather;
use App\Models\GatherGoldLogs;
use App\Models\GatherShoppingCard;
use App\Models\GatherTrade;
use App\Models\GatherUsers;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Log;
class GatherService
{
    /**参加拼团
     * @param int $gid
     * @param int $uid
     * @return mixed
     * @throws LogicException
     */
    public function addGatherUser (int $gid, int $uid)
    {
        $userRatio = 6;
        $userSum = (new Gather())->getGatherUserSum($gid);
        //判断拼团是否达到开团人数
        if ($userSum == $userRatio)
        {
            throw new LogicException('本拼团参团人数已满');
        }
        //判断用户金额
        //判断用户当天当场次最多5次，每人每天最多30次
        (new GatherService())->isMaxSum($gid, $uid);
        DB::beginTransaction();
        try {
            //新增用户参团记录
            $gatherUsersData = (new GatherUsers())->setGatherUsers($gid, $uid);
            //新增来拼金记录
            (new GatherGoldLogs())->setGatherGold($gid, $uid, $gatherUsersData->id);
            //判断是否开团、开奖
            $this->isMaxGatherUser($gid);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
        DB::commit();
        return json_encode(['code' => 200, 'msg' => '参团成功！']);
    }

    /**判断用户参加拼团的次数
     * @param int $gid
     * @param int $uid
     * @return mixed
     * @throws LogicException
     */
    public function isMaxSum (int $gid, int $uid)
    {
        $gatherRatio = 5;
        $gatherAllRatio = 30;
        $gatherSum = (new GatherUsers())->getGatherUserSum($gid, $uid);
        if ($gatherSum >= $gatherRatio)
        {
            throw new LogicException('本场次拼团活动已超过最大可参与次数5次');
        }

        $gatherAllSum = (new GatherUsers())->getUserAllSum($uid);
        if ($gatherAllSum >= $gatherAllRatio)
        {
            throw new LogicException('已超过每天最大可参与次数30次');
        }
    }

    /**判断拼团是否到达开团人数
     * @param int $gid
     * @return mixed
     * @throws LogicException
     */
    public function isMaxGatherUser (int $gid)
    {
        $userRatio = 6;
        $userSum = (new Gather())->getGatherUserSum($gid);
        $status = 1;
        //判断拼团是否达到开团人数
        if ($userSum == $userRatio)
        {
            //更新拼团为开启状态
            (new Gather())->updGather($gid, $status);
            //更新用户参团的拼团状态
            (new GatherGoldLogs())->updGatherGold($gid, $status);
            //开奖
            $this->lotteryDraw($gid);
        } else
        {
            //72H 仍未满足开团人数，直接关闭该拼团
            //$gatherData = (new Gather())->getGatherInfo();
        }
    }

    /**开奖随机算法
     * @param int $gid
     * @return mixed
     * @throws LogicException
     */
    public function lotteryDraw (int $gid)
    {
        $numRatio = 5;

        //获取平团用户数据
        $oneGatherUsers = json_decode((new GatherUsers())->getGatherUserList($gid), 1);
        //打乱数组
        shuffle($oneGatherUsers);
        $gatherUsersArr = array_column($oneGatherUsers, null, 'id');
        $guidDict = array_column($oneGatherUsers, 'id');

        //随机取出5条数据
        $guids = array_rand($gatherUsersArr, $numRatio);

        //获取未获奖的参团用户id
        $diffGuids = array_diff($guidDict, $guids);

        //获奖操作
        $this->awardUsers($guids);

        //未获奖操作
        $this->noAwardUsers($diffGuids);

        //更新来拼金状态
        (new GatherGoldLogs())->updGatherGoldType($diffGuids, 0);
    }

    /**获奖用户操作
     * @param array $data
     * @return mixed
     * @throws LogicException
     */
    public function awardUsers (array $data)
    {
        $moneyRatio = 100;
        $date = date('Y-m-d H:i:s');
        //更新用户开奖状态
        (new GatherUsers())->updGatherUserType($data);

        //获取中奖用户信息
        $gatherUserList = (new GatherUsers())->getGatherUserArr($data);
        $gatherUserData = json_decode($gatherUserList, 1);

        //获奖用户新增购物卡
        foreach ($gatherUserData as &$val)
        {
            $val['guid'] = $val['id'];
            $val['money'] = $moneyRatio;
            $val['status'] = 1;
            $val['created_at'] = $date;
            $val['updated_at'] = $date;
            unset($val['id']);
        }
        (new GatherShoppingCard())->setGatherShoppingCard($gatherUserData);
    }

    /**未获奖用户操作
     * @param array $data
     * @return mixed
     * @throws LogicException
     */
    public function noAwardUsers (array $data)
    {
        $date = date('Y-m-d H:i:s');

        $price = 20;
        $profitRatio = 5;
        $name = '拼团补贴';
        //获奖用户新增购物卡
        $orderData = [];
        $orderNoData = [];

        //获取未中奖用户参团信息
        $gatherUserList = (new GatherUsers())->getGatherUserArr($data);
        $gatherOrderData = $gatherTradeData = json_decode($gatherUserList, 1);

        //新增订单数据
        foreach ($gatherOrderData as &$val)
        {
            $orderNo = createOrderNo();
            $val['business_uid'] = 2;
            $val['profit_ratio'] = $profitRatio;
            $val['price'] = $price;
            $val['profit_price'] = $price * $profitRatio / 100;
            $val['status'] = 1;
            $val['name'] = $name;
            $val['remark'] = '';
            $val['state'] = 1;
            $val['pay_status'] = 'await';
            $val['to_be_added_integral'] = 0;
            $val['to_status'] = 0;
            $val['line_up'] = 0;
            $val['order_no'] = $orderNo;
            $val['created_at'] = $date;
            $val['updated_at'] = $date;
            $orderNoData[] = $orderNo;
            unset($val['id'], $val['gid']);
        }
        (new Order())->setGatherOrder($gatherOrderData);

        //获取未中奖用户录单信息
        $orderInfo = (new Order())->getGatherOrder($orderNoData);
        $orderList = json_decode($orderInfo, 1);

        //新增未获奖用户录单记录
        $gatherTradeList = array_merge($gatherTradeData, $orderList);
        dd($gatherTradeList);
        foreach ($gatherTradeList as &$value)
        {
            $value['guid'] = $value['id'];
            $value['profit_ratio'] = $profitRatio;
            $value['price'] = $price;
            $value['profit_price'] = $price * $profitRatio / 100;
            $value['status'] = 1;
            $val['created_at'] = $date;
            $val['updated_at'] = $date;
            unset($val['id']);
        }
        (new GatherTrade())->setGatherTrade($gatherTradeList);
    }
}
