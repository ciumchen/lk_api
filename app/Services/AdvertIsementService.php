<?php

namespace App\Services;

use App\Exceptions\LogicException;
use App\Models\AdvertTrade;
use App\Models\AdvertUsers;
use App\Models\Order;
use App\Models\Setting;
use App\Models\Users;
use Illuminate\Support\Facades\DB;

class AdvertIsementService
{
    const CHANNEL_ID = 20030;
    /**新增用户广告收入记录
     * @param array $data
     * @return mixed
     * @throws
     */
    public function addUsereIncome (array $data)
    {
        //签名
        $param = $data['award'] . $data['packagename'] . $data['type'] . $data['uid'] . $data['unique_id'];
        $checkSign = strtolower(md5($param . md5(self::CHANNEL_ID)));
        //dd($checkSign);

        //判断签名是否一致
        if ($checkSign != $data['sign'])
        {
            return json_encode(['status' => 10000, 'msg' => '签名不合法，非法操作！']);
        }

        //数据是否已存在
        $where = [
            'unique_id' => $data['unique_id'],
        ];
        $userAdvertInfo = (new AdvertUsers())->getUserAdvert($where);
        if ($userAdvertInfo)
        {
            return json_encode(['status' => 10000, 'msg' => '已发放广告奖励！']);
        }

        DB::beginTransaction();
        try {
            //新增用户广告记录
            (new AdvertUsers())->setUserAdvert($data);
            //更新用户广告奖励
            (new Users())->updAdvertAward($data['uid'], $data['award']);
        } catch (\Exception $e) {
            DB::rollBack();
            throw new LogicException('发放广告奖励失败！');
        }
        DB::commit();

        return json_encode(['status' => 1, 'msg' => '发放广告奖励成功！']);
    }

    /**新增用户广告兑换记录
     * @param int $uid
     * @return mixed
     * @throws
     */
    public function addTakeAward (int $uid)
    {
        $profitRatio = 20;
        $name = '广告补贴';
        $orderNo = createOrderNo();
        //获取用户当前广告奖励金额
        $userAward = (new Users())->getUserValue($uid, 'advert_award');
        if ($userAward < 20)
        {
            return json_encode(['code' => 10000, 'msg' => '额度不足，无法兑换！']);
        }

        //组装order 表数据
        $orderData = [
            'uid'          => $uid,
            'business_uid' => 2,
            'profit_ratio' => $profitRatio,
            'price'        => $userAward,
            'profit_price' => $userAward * $profitRatio / 100,
            'status'       => 1,
            'name'         => $name,
            'pay_status'   => 'await',
            'order_no'     => $orderNo,
            'description'  => 'GLR',
        ];

        //组装advert_trade 表数据
        $advertTrade = [
            'uid'          => $uid,
            'business_uid' => 2,
            'profit_ratio' => $profitRatio,
            'price'        => $userAward,
            'profit_price' => $userAward * $profitRatio / 100,
            'order_no'     => $orderNo,
            'status'       => 1,
        ];

        DB::beginTransaction();
        try {
            //更新用户广告奖励金额
            (new Users())->updAdvertAward($uid, -$userAward);
            //生成广告用户录单订单记录
            $orderInfo = (new Order())->addOrder($orderData);
            //生成广告用户录单记录
            $advertTrade['oid'] = $orderInfo->id;
            (new AdvertTrade())->setAdvertTrade($advertTrade);
            //增加录单积分
            (new GatherOrderService())->completeOrderGatger($orderInfo->id, $uid, 'GLR', $orderNo);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
        DB::commit();

        return json_encode(['code' => 200, 'msg' => '兑换成功！']);
    }

    /**新增用户拼团广告记录
     * @param array $data
     * @return mixed
     * @throws
     */
    public function addGatherAdvert (array $data)
    {
        //签名
        $param = $data['award'] . $data['packagename'] . $data['type'] . $data['uid'] . $data['unique_id'];
        $checkSign = strtolower(md5($param . md5(self::CHANNEL_ID)));
        //dd($checkSign);

        //判断签名是否一致
        if ($checkSign != $data['sign'])
        {
            return json_encode(['status' => 10000, 'msg' => '签名不合法，非法操作！']);
        }

        //判断每天拼团广告次数
        $advertRatio = 30;
        $advertSum = (new AdvertUsers())->getGatherAdvertSum($data['uid']);
        if ($advertSum >= $advertRatio)
        {
            return json_encode(['status' => 10000, 'msg' => '已超过每天额外获取拼团次数最大值！']);
        }

        //数据是否已存在
        $where = [
            'unique_id' => $data['unique_id'],
        ];
        $userAdvertInfo = (new AdvertUsers())->getUserAdvert($where);
        if ($userAdvertInfo)
        {
            return json_encode(['status' => 10000, 'msg' => '数据已经存在！']);
        }

        DB::beginTransaction();
        try {
            //新增用户广告记录
            (new AdvertUsers())->setUserAdvert($data);
        } catch (\Exception $e) {
            DB::rollBack();
            throw new LogicException('观看广告失败！');
        }
        DB::commit();

        return json_encode(['status' => 1, 'msg' => '观看广告成功！']);
    }
}
