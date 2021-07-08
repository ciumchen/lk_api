<?php

namespace App\Services;

use App\Models\ConvertLogs;
use App\Models\Order;
use App\Services\bmapi\MobileRechargeService;
use App\Services\OrderService;
use Exception;
use Illuminate\Support\Facades\DB;

/** 兑换充值 **/

class ConvertService
{
    /**usdt 兑换话费
    * @param array $data
    * @return mixed
    * @throws
    */
    public function phoneBill(array $data)
    {
        DB::beginTransaction();
        try
        {
            //组装数据
            $data['orderNo'] = createOrderNo();
            $data['user_name'] = '';
            $data['type'] = 1;

            //新增兑换数据
            $data['orderName'] = '兑换话费';
            $this->commonConvert($data);

            //新增充值记录
            (new MobileRechargeService)->addMobileOrder($data['orderNo'], $data['uid'], $data['phone'], $data['price']);
            //调用话费充值
            (new MobileRechargeService)->convertRecharge($data['orderNo']);
            
        } catch (Exception $e)
        {
            throw $e;
            DB::rollBack();
        }
        DB::commit();
    }

    /**usdt 兑换美团
    * @param array $data
    * @return mixed
    * @throws
    */
    public function meituanBill(array $data)
    {
        DB::beginTransaction();
        try
        {
            //组装数据
            $data['orderNo'] = createOrderNo();
            $data['user_name'] = $data['userName'];
            $data['type'] = 2;

            //新增兑换数据
            $data['orderName'] = '兑换额度（美团）';
            $this->commonConvert($data); 
        } catch (Exception $e)
        {
            throw $e;
            DB::rollBack();
        }
        DB::commit();
    }

    /**usdt 兑换
    * @param array $data
    * @return mixed
    * @throws
    */
    public function commonConvert(array $data)
    {
        //插入数据到兑换记录
        (new ConvertLogs())->setConvert($data);

        //插入数据到变动记录
        $data['remark'] = '兑换话费';
        (new ConvertLogs())->setAssetsLogs($data);

        //更新用户资产数据
        (new ConvertLogs())->updAssets($data);

        //order 表增加订单记录
        $ratio = 5;
        $profitPrice = $data['price'] * $ratio / 100;
        (new Order())->setOrderSelf($data['uid'], 2, $ratio, $data['price'], $profitPrice, 
        $data['orderNo'], $data['orderName'], 1, 'await', 'convert');
        //更新order 表审核状态
        (new OrderService())->completeBmOrder($data['orderNo']);
    }
}