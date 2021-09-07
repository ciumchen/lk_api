<?php

namespace App\Services;

use App\Exceptions\LogicException;
use App\Jobs\AddGatherUsers;
use App\Jobs\SendGatherLottery;
use App\Models\AdvertUsers;
use App\Models\Gather;
use App\Models\GatherGoldLogs;
use App\Models\GatherShoppingCard;
use App\Models\GatherTrade;
use App\Models\GatherUsers;
use App\Models\Order;
use App\Models\Setting;
use App\Models\Users;
use Illuminate\Support\Facades\DB;

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
        //获取设置拼团总人数
        $userRatio = Setting::getSetting('gather_users_ number') ?? 100;
        //$userRatio = 6;
        //获取当前拼团人数
        $userScaler = (new Gather())->getUsersNum($gid);
        $userSum = (new Gather())->getGatherUserSum($gid);
        //获取用户来拼金总数
        $userGoldSum = (new GatherUsers())->getUserGold($uid);
        //获取用户来拼金扣减总数
        $minusSum = (new GatherGoldLogs())->minusUserGold($uid);

        //判断拼团是否达到开团人数
        if ($userScaler >= $userRatio || $userSum >= $userRatio)
        {
            return json_encode(['code' => 10000, 'msg' => '本拼团参团人数已满！']);
        }

        //判断用户金额
        if ($userGoldSum <= $minusSum)
        {
            return json_encode(['code' => 10000, 'msg' => '账户来拼金余额已不足，请及时充值！']);
        }

        DB::beginTransaction();
        try {
            //判断用户当天当场次最多5次，每人每天最多30次
            (new GatherService())->isMaxSum($gid, $uid);
            //更新拼团参与人数
            (new Gather())->updUsersNum($gid);
            //新增用户参团记录
            $gatherUsersData = (new GatherUsers())->setGatherUsers($gid, $uid);
            //新增来拼金记录
            (new GatherGoldLogs())->setGatherGold($gid, $uid, $gatherUsersData->id);
            //判断是否开团、开奖
            $this->isMaxGatherUser($gid, $userRatio);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

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
        //每天拼团最多次数
        $gatherAllRatio = 30;
        $gatherSum = (new GatherUsers())->getGatherUserSum($gid, $uid);
        $userCount = (new GatherUsers())->getUserOneSum($gid, $uid);
        if ($gatherSum >= $gatherRatio || $userCount >=$gatherRatio)
        {
            throw new LogicException('本场次拼团活动已超过最大可参与次数5次!');
        }

        //获取每天拼团广告次数
        $advertSum = (new AdvertUsers())->getGatherAdvertSum($uid);
        //获取每天拼团次数
        $gatherAllSum = (new GatherUsers())->getUserAllSum($uid);
        if ($gatherAllSum >= $gatherAllRatio + $advertSum)
        {
            throw new LogicException('已超过每天最大可参与次数！');
        }
    }

    /**判断拼团是否到达开团人数
     * @param int $gid
     * @param int $userRatio
     * @return mixed
     * @throws LogicException
     */
    public function isMaxGatherUser (int $gid, int $userRatio)
    {
        $userSum = (new Gather())->getGatherUserSum($gid);
        $status = 1;
        //判断拼团是否达到开团人数
        if ($userSum == $userRatio)
        {
            try {
                //更新拼团为开启状态
                (new Gather())->updGather($gid, $status);
                //更新用户参团的拼团状态
                (new GatherGoldLogs())->updGatherGold($gid, $status);
                //开启拼团
                $this->lotteryDraw($gid);
            } catch (\Exception $e) {
                throw $e;
            }
        }
    }

    /**开奖随机算法
     * @param int $gid
     * @return mixed
     * @throws LogicException
     */
    public function lotteryDraw (int $gid)
    {
        $numRatio = Setting::getSetting('gather_luck_number') ?? 5;
        $gatherStatus = 3;

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

        DB::beginTransaction();
        try {
            //获奖操作
            $this->awardUsers($guids);

            //更新来拼金状态
            (new GatherGoldLogs())->updGatherGoldType($diffGuids, 0);

            //更新拼团状态
            (new Gather())->updGather($gid, $gatherStatus);

            //更新来拼金拼团状态
            (new GatherGoldLogs())->updGatherGold($gid, $gatherStatus);

            //新增拼团
            $gatherInfo = (new Gather())->getGatherInfo($gid);
            (new Gather())->setGather($gatherInfo->type);

            //未获奖操作
            $this->noAwardUsers($diffGuids);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**获奖用户后续操作
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

        //获取中奖用户来拼金账户余额
        $guidDict = array_column($gatherUserData, 'id');
        $uidDict = array_column($gatherUserData, 'uid');
        $userGoldData = (new GatherUsers())->getUsersGold($guidDict);

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

        //更新用户来拼金余额
        (new GatherGoldLogs())->updUsersGold($moneyRatio, $userGoldData);

        DB::beginTransaction();
        try {
            //新增中奖用户购物卡记录
            (new GatherShoppingCard())->setGatherShoppingCard($gatherUserData);
            //更新用户购物卡金额
            (new Users())->updGatherCard($uidDict, $moneyRatio);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
        DB::commit();
    }

    /**未获奖用户后续操作
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
        $orderNoData = [];

        //获取未中奖用户参团信息
        $gatherUserList = (new GatherUsers())->getGatherUserArr($data);
        $orderData = $gatherTradeData = json_decode($gatherUserList, 1);

        $newGatherTrade = [];
        //新增订单数据
        foreach ($orderData as &$val)
        {
            //组装order 表数据
            $orderNo = createOrderNo();
            $val['business_uid'] = 2;
            $val['profit_ratio'] = $profitRatio;
            $val['price'] = $price;
            $val['profit_price'] = $price * $profitRatio / 100;
            $val['status'] = 1;
            $val['name'] = $name;
            $val['state'] = 1;
            $val['pay_status'] = 'await';
            $val['to_be_added_integral'] = 0;
            $val['to_status'] = 0;
            $val['line_up'] = 0;
            $val['order_no'] = $orderNo;
            $val['description'] = 'PT';
            $val['created_at'] = $date;
            $val['updated_at'] = $date;
            $orderNoData[] = $orderNo;

            //组装gather_trade 表数据
            $newGatherTrade[] = [
                'guid'         => $val['id'],
                'gid'          => $val['gid'],
                'uid'          => $val['uid'],
                'business_uid' => 2,
                'profit_ratio' => $profitRatio,
                'price'        => $price,
                'profit_price' => $price * $profitRatio / 100,
                'order_no'     => $orderNo,
                'status'       => 1,
                'created_at'   => $date,
                'updated_at'   => $date
            ];
            unset($val['id'], $val['gid']);
        }

        DB::beginTransaction();
        try {
            //生成未中奖用户录单订单记录
            (new Order())->setGatherOrder($orderData);
            //生成未中奖用户录单拼团记录
            (new GatherTrade())->setGatherTrade($newGatherTrade);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
        DB::commit();

        //获取未中奖用户录单信息
        $orderInfo = (new Order())->getGatherOrder($orderNoData);
        $orderList = json_decode($orderInfo, 1);

        //录单自动审核、加积分
        //$this->completeOrderGather($orderList);
        //更新未中奖用户录单拼团记录信息
        //$this->updGatherTrade($orderList);
        //把订单数据加到队列，执行录单自动审核、加积分，更新未中奖用户录单拼团记录信息
        foreach ($orderList as $list)
        {
            $jobs = new SendGatherLottery($list);
            $jobs->dispatch($jobs)->onQueue('sendGatherLottery');
        }
    }

    /**更新未中奖用户录单拼团记录信息
     * @param array $gatherTradeData
     * @return mixed
     * @throws LogicException
     */
    /*public function updGatherTrade (array $gatherTradeData)
    {
        foreach ($gatherTradeData as $list)
        {
            (new GatherTrade())->updGatherTrade(['order_no' => $list['order_no']], ['oid' => $list['oid']]);
        }
    }*/
    public function updGatherTrade (array $gatherTradeData)
    {
        (new GatherTrade())->updGatherTrade(['order_no' => $gatherTradeData['order_no']], ['oid' => $gatherTradeData['oid']]);
    }

    /**未获奖用户录单后续操作
     * @param array $orderData
     * @return mixed
     * @throws LogicException
     */
    /*public function completeOrderGather (array $orderData)
    {
        foreach ($orderData as $data)
        {
            (new GatherOrderService())->completeOrderGatger($data['oid'], $data['uid'], 'PT', $data['order_no']);
        }
    }*/
    public function completeOrderGather (array $orderData)
    {
        (new GatherOrderService())->completeOrderGatger($orderData['oid'], $orderData['uid'], 'PT', $orderData['order_no']);
    }
}
