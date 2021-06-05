<?php

namespace App\Services\bmapi;

use App\Exceptions\LogicException;
use DB;
use App\Models\Order;
use App\Models\OrderMobileRecharge;
use App\Models\TradeOrder;
use Bmapi\Api\MobileRecharge\GetItemInfo;
use Bmapi\Api\MobileRecharge\PayBill;
use Exception;

class MobileRechargeService
{
    
    public function setAllOrder($user, $mobile, $money)
    {
        try {
            $this->bmMobileRechargeCheck($mobile, $money);
        } catch (Exception $e) {
            throw $e;
        }
        $Order = new Order();
        $TradeOrder = new TradeOrder();
        $order_no = $TradeOrder->CreateOrderNo();
        $order_data = $this->createOrderParams($user, $money);
        DB::beginTransaction();
        try {
            /* 生成 order 表数据 */
            $order_id = $Order->setOrder($order_data);
            /* 生成 order_mobile_recharge 表数据 */
            $this->setMobileOrder($order_id, $order_no, $user->id, $mobile, $money);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
        DB::commit();
        return $Order->find($order_id);
    }
    
    /**
     * Order 表数据组装
     *
     * @param $user
     * @param $money
     *
     * @return array
     */
    public function createOrderParams($user, $money)
    {
        $profit_ratio = 5;
        $profit_price = $money * ($profit_ratio / 100);
        return [
            'uid'          => $user->id,
            'business_uid' => 2,
            'profit_ratio' => $profit_ratio,
            'price'        => $money,
            'profit_price' => $profit_price,
            'name'         => '话费',
            'status'       => '1',
            'pay_status'   => 'await',
            'remark'       => '1',
        ];
    }
    
    /**
     * 斑马手机充值检查
     * 订单生成前调用
     *
     * @param string $mobile
     * @param float  $money
     *
     * @return bool
     * @throws \Exception
     */
    public function bmMobileRechargeCheck($mobile, $money)
    {
        $GetItemInfo = new GetItemInfo();
        try {
            $GetItemInfo->setMobileNo($mobile)
                        ->setRechargeAmount($money)
                        ->getResult();
            $info = $GetItemInfo->getItemInfo();
            if (empty($info) || intval($info[ 'numberChoice' ]) < 1) {
                throw new Exception('手机号不支持此金额充值，请选择其它金额');
            }
        } catch (Exception $e) {
            throw $e;
        }
        return true;
    }
    
    /**
     * 手机充值
     *
     * @param string $mobile
     * @param float  $money
     * @param string $order_no
     * @param string $notify_url
     *
     * @return array
     * @throws \Exception
     */
    public function bmMobileRecharge($mobile, $money, $order_no, $notify_url = '')
    {
        if (empty($notify_url)) {
            $notify_url = url('/api/mobile-notify');
        }
        $PayBill = new PayBill();
        try {
            $PayBill->setMobileNo($mobile)
                    ->setRechargeAmount($money)
                    ->setCallback($notify_url)
                    ->setOuterTid($order_no)
                    ->getResult();
            $bill = $PayBill->getBill();
        } catch (Exception $e) {
            throw  $e;
        }
        return $bill;
    }
    
    /**
     * @param int    $order_id 订单[order]表ID
     * @param string $order_no 订单号
     * @param int    $uid      用户ID
     * @param string $mobile   充值电话
     * @param float  $money    充值金额
     *
     * @return mixed
     * @throws \Exception
     */
    public function setMobileOrder($order_id, $order_no, $uid, $mobile, $money)
    {
        $date = date('Y-m-d H:i:s');
        $data = [
            'mobile'     => $mobile,
            'money'      => $money,
            'order_id'   => $order_id,
            'order_no'   => $order_no,
            'created_at' => $date,
            'updated_at' => $date,
            'uid'        => $uid,
        ];
        $mobileOrder = new OrderMobileRecharge();
        try {
            $mobileOrder->save($data);
        } catch (Exception $e) {
            throw  $e;
        }
        return $mobileOrder;
    }
    
    public function updateMobileOrder($order_id, $bill)
    {
        $data = [
            'mobile'      => $bill[ 'rechargeAccount' ],
            'money'       => $bill[ 'saleAmount' ],
            'order_no'    => $bill[ 'outerTid' ],
            'updated_at'  => $bill[ 'operateTime' ],
            'trade_no'    => $bill[ 'billId' ],
            'status'      => $bill[ 'rechargeState' ],
            'pay_status'  => $bill[ 'payState' ],
            'goods_title' => $bill[ 'itemName' ],
        ];
    }
}
