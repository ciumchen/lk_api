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
    
    /**
     * 生成充值订单
     *
     * @param \App\Models\User $user   付款用户数据模型
     * @param string           $mobile 充值手机
     * @param float            $money  充值金额
     *
     * @return mixed
     * @throws \Exception
     */
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
        $order_data = $this->createOrderParams($user, $money, $order_no);
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
     * @param \App\Models\User $user     充值用户模型数据
     * @param float            $money    充值金额
     *
     * @param string           $order_no 订单号
     *
     * @return array
     */
    public function createOrderParams($user, $money, $order_no)
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
            'remark'       => '',
            'order_no'     => $order_no,
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
     * 订单充值
     * 付款成功后调用
     *
     * @param int    $order_id 订单ID
     * @param string $order_no 订单编号
     *
     * @throws \Exception
     */
    public function recharge($order_id, $order_no)
    {
        $MobileOrder = new OrderMobileRecharge();
        $mobileOrderInfo = $MobileOrder->where('order_id', '=', $order_id)
                                       ->first();
        try {
            /* 调用充值 */
            $bill = $this->bmMobileRecharge($mobileOrderInfo->mobile, $mobileOrderInfo->money, $order_no);
            /* 更新订单 */
            $this->updateMobileOrder($order_id, $bill);
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
     * 生成手机充值订单
     *
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
        $MobileOrder = new OrderMobileRecharge();
        try {
            $MobileOrder->mobile = $mobile;
            $MobileOrder->money = $money;
            $MobileOrder->order_id = $order_id;
            $MobileOrder->order_no = $order_no;
            $MobileOrder->created_at = $date;
            $MobileOrder->updated_at = $date;
            $MobileOrder->uid = $uid;
            $MobileOrder->save();
        } catch (Exception $e) {
            throw  $e;
        }
        return $MobileOrder;
    }
    
    /**
     * 手机充值订单表更新
     *
     * @param int                                  $order_id       订单ID
     * @param array                                $bill           第三方返回账单信息
     * @param \App\Models\OrderMobileRecharge|null $MobileRecharge 手机充值记录表
     *
     * @throws \Exception
     */
    public function updateMobileOrder($order_id, $bill, OrderMobileRecharge $MobileRecharge = null)
    {
        if ($MobileRecharge == null) {
            $MobileRecharge = OrderMobileRecharge::where('order_id', '=', $order_id)
                                                 ->first();
        }
        try {
            $MobileRecharge->mobile = $bill[ 'rechargeAccount' ];
            $MobileRecharge->money = $bill[ 'saleAmount' ];
            $MobileRecharge->order_no = $bill[ 'outerTid' ];
            $MobileRecharge->updated_at = $bill[ 'operateTime' ];
            $MobileRecharge->trade_no = $bill[ 'billId' ];
            $MobileRecharge->status = $bill[ 'rechargeState' ];
            $MobileRecharge->pay_status = $bill[ 'payState' ];
            $MobileRecharge->goods_title = $bill[ 'itemName' ];
            $MobileRecharge->save();
        } catch (Exception $e) {
            throw $e;
        }
    }
}
