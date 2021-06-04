<?php

namespace App\Services\bmapi;

use App\Exceptions\LogicException;
use Bmapi\Api\MobileRecharge\GetItemInfo;
use Bmapi\Api\MobileRecharge\PayBill;
use Exception;

class MobileRechargeService
{
    
    /**
     * 斑马手机充值检查
     *
     * @param string $mobile
     * @param float  $money
     *
     * @return bool
     * @throws \App\Exceptions\LogicException
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
            /*TODO:生成订单和手机充值订单*/
        } catch (Exception $e) {
            throw new LogicException($e->getMessage());
        }
        return true;
    }
    
    /**
     * @param string $mobile
     * @param float  $money
     * @param string $order_no
     * @param string $notify_url
     *
     * @throws \App\Exceptions\LogicException
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
            throw new LogicException($e->getMessage());
        }
    }
}
