<?php

namespace App\Services;

use App\Models\ConvertLogs;
use App\Models\Order;
use App\Services\bmapi\MobileRechargeService;
use App\Services\OrderService;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Exceptions\LogicException;
use App\Models\Assets;
use App\Models\Setting;

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
        $reg = '/^1[3456789]\d{9}$/';
        if (preg_match($reg, $data['phone']) < 1)
        {
            throw new LogicException('手机号格式不正确');
        }
        DB::beginTransaction();
        try
        {
            //组装数据
            $data['orderNo'] = createOrderNo();
            $data['user_name'] = '';
            $data['type'] = 1;
            $data['remark'] = '兑换话费';

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
        return json_encode(['code' => 200, 'msg' => '兑换话费充值成功']);
    }

    /**usdt 兑换美团
    * @param array $data
    * @return mixed
    * @throws
    */
    public function meituanBill(array $data)
    {
        $reg = '/^1[3456789]\d{9}$/';
        if (preg_match($reg, $data['phone']) < 1)
        {
            throw new LogicException('手机号格式不正确');
        }
        DB::beginTransaction();
        try
        {
            //组装数据
            $data['orderNo'] = createOrderNo();
            $data['user_name'] = $data['userName'];
            $data['type'] = 2;
            $data['remark'] = '兑换额度（美团）';

            //新增兑换数据
            $data['orderName'] = '兑换额度（美团）';
            $this->commonConvert($data);

            //更新充值状态
            ConvertLogs::where(['order_no' => $data['orderNo']])
                        ->update(['status' => 2, 'updated_at' => date('Y-m-d H:i:s')]);
        } catch (Exception $e)
        {
            throw $e;
            DB::rollBack();
        }
        DB::commit();
        return json_encode(['code' => 200, 'msg' => '兑换额度（美团）充值成功']);
    }

    /**usdt 兑换
    * @param array $data
    * @return mixed
    * @throws
    */
    public function commonConvert(array $data)
    {
        //获取当前用户可用 usdt 金额
        $atAmount = (new Assets())->getUsdtAmount($data['uid']);
        $data['atAmount'] = $atAmount['amount'];

        //插入数据到兑换记录
        (new ConvertLogs())->setConvert($data);

        //插入数据到变动记录
        (new ConvertLogs())->setAssetsLogs($data);

        //更新用户资产数据
        (new ConvertLogs())->updAssets($data);

        //order 表增加订单记录
        $ratio = Setting::getSetting('set_business_rebate_scale_cl');
        $profitPrice = $data['price'] * $ratio / 100;
        $order = (new Order())->setOrderSelf($data['uid'], 2, $ratio, $data['price'], $profitPrice,
        $data['orderNo'], $data['orderName'], 1, 'await', 'convert');
        //更新convert_logs 表 oid 字段
        (new ConvertLogs)->updOid($order->order_no, $order->id);

        //更新order 表审核状态
        (new OrderService())->completeBmOrder($data['orderNo']);
    }
}
