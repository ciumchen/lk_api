<?php

namespace App\Services\bmapi;

use App\Models\Setting;
use DB;
use App\Models\Order;
use App\Models\OrderMobileRecharge;
use App\Models\TradeOrder;
use Bmapi\Api\MobileRecharge\GetItemInfo;
use Bmapi\Api\MobileRecharge\PayBill;
use Exception;
use Log;

class MobileRechargeService extends BaseService
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
     * @throws \Throwable
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
            /* 生成 trade_order 表数据 */
            $trade_order_date = $this->createTradeOrderParams($user, $money, $order_no, $mobile, $order_id);
            $TradeOrder->setOrder($trade_order_date);
            /* 生成 order_mobile_recharge 表数据 */
            $this->setMobileOrder($order_id, $order_no, $user->id, $mobile, $money);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
        DB::commit();
        return $Order->find($order_id);
    }

    /**
     * @param $user
     * @param $mobile
     * @param $money
     *
     * @return mixed
     * @throws \Throwable
     */
    public function setDlAllOrder($user, $mobile, $money)
    {
        try {
            $this->bmMobileRechargeCheck($mobile, $money);
        } catch (Exception $e) {
            throw $e;
        }
        $Order = new Order();
        $TradeOrder = new TradeOrder();
        $order_no = $TradeOrder->CreateOrderNo();
        $dl_order_data = $this->createDlOrderParams($user, $money, $order_no);
        DB::beginTransaction();
        try {
            /* 生成 order 表数据 */
            $order_id = $Order->setOrder($dl_order_data);
            /* 生成 trade_order 表数据 */
            $dl_trade_order_data = $this->createDlTradeOrderParams($user, $money, $order_no, $mobile, $order_id);
            $TradeOrder->setOrder($dl_trade_order_data);
            /* 生成 order_mobile_recharge 表数据 */
            (new OrderMobileRecharge)->setDlMobileOrder($order_id, $order_no, $user->id, $mobile, $money);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
        Db::commit();
        return $Order->find($order_id);
    }

    /**
     * 话费代充订单数据创建
     *
     * @param $user
     * @param $money
     * @param $order_no
     *
     * @return array
     */
    public function createDlOrderParams($user, $money, $order_no)
    {
        $date = date("Y-m-d H:i:s");
        $profit_ratio = Setting::where('key','set_business_rebate_scale_zl')->value('value');
        $profit_price = $money * ($profit_ratio / 100);
        return [
            'uid'          => $user->id,
            'business_uid' => 2,
            'profit_ratio' => $profit_ratio,
            'price'        => $money,
            'profit_price' => $profit_price,
            'name'         => '代充',
            'created_at'   => $date,
            'status'       => '1',
            'state'        => '1',
            'pay_status'   => 'await',
            'remark'       => '',
            'order_no'     => $order_no,
        ];
    }

    /**
     * trade_order 表代充订单数据创建
     *
     * @param $user
     * @param $money
     * @param $order_no
     *
     * @return array
     */
    public function createDlTradeOrderParams($user, $money, $order_no, $mobile, $order_id)
    {
        $date = date("Y-m-d H:i:s");
        $profit_ratio = (Setting::where('key','set_business_rebate_scale_zl')->value('value'))/100;
        $profit_price = $money * $profit_ratio;
        return [
            'user_id'       => $user->id,
            'title'         => '话费代充',
            'price'         => $money,
            'num'           => 1,
            'numeric'       => $mobile,
            'telecom'       => '代充',
            'status'        => 'await',
            'order_no'      => $order_no,
            'need_fee'      => $money,
            'profit_ratio'  => $profit_ratio,
            'profit_price'  => $profit_price,
            'integral'      => 0.00,
            'description'   => 'ZL',
            'oid'           => $order_id,
            'created_at'    => $date,
            'pay_time'      => $date,
            'modified_time' => $date,
            'remarks'       => '',
            'order_from'    => '',
        ];
    }

    /**
     * TradeOrder 表数据组装
     *
     * @param $user
     * @param $mobile
     * @param $money
     * @param $order_id
     * @param $order_no
     *
     * @return array
     */
    public function createTradeOrderParams($user, $money, $order_no, $mobile, $order_id)
    {
        $date = date("Y-m-d H:i:s");
        $profit_ratio = 0.05;
        $profit_price = $money * $profit_ratio;
        return [
            'user_id'       => $user->id,
            'title'         => '话费充值',
            'price'         => $money,
            'num'           => 1,
            'numeric'       => $mobile,
            'telecom'       => '话费',
            'status'        => 'await',
            'order_no'      => $order_no,
            'need_fee'      => $money,
            'profit_ratio'  => $profit_ratio,
            'profit_price'  => $profit_price,
            'integral'      => 0.00,
            'description'   => 'HF',
            'oid'           => $order_id,
            'created_at'    => $date,
            'pay_time'      => $date,
            'modified_time' => $date,
            'remarks'       => '',
            'order_from'    => '',
        ];
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
        $date = date("Y-m-d H:i:s");
        $profit_ratio = 5;
        $profit_price = $money * ($profit_ratio / 100);
        return [
            'uid'          => $user->id,
            'business_uid' => 2,
            'profit_ratio' => $profit_ratio,
            'price'        => $money,
            'profit_price' => $profit_price,
            'name'         => '话费',
            'created_at'   => $date,
            'status'       => '1',
            'state'        => '1',
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
                throw new Exception('请选择其它充值金额');
            }
        } catch (Exception $e) {
            throw $e;
        }
        return true;
    }

    /**
     * 回调处理订单状态
     *
     * @param $data
     *
     * @throws \Exception
     */
    public function notify($data)
    {
        /*
       {
       "user_id": "A5626842",
       "sign": "C0F9E3501C0DB8EBA781993D8268B073FBF9EE79",
       "recharge_state": "1",
       "outer_tid": "PY_20210605210408281427",
       "tid": "S2106052397812",
       "timestamp": "2021-06-05 21:05:12"
       }
       */
        $MobileRecharge = new OrderMobileRecharge();
        try {
            if (empty($data)) {
                throw new Exception('手机充值回调数据为空');
            }
            $PayBill = new PayBill();
            if (!$PayBill->checkSign($data)) {
                throw new Exception('验签不通过');
            }
            $rechargeInfo = $MobileRecharge->where('order_no', '=', $data[ 'outer_tid' ])
                                           ->first();
            if (empty($rechargeInfo)) {
                throw new Exception('未查询到订单数据');
            }
            if ($rechargeInfo->status != 0) {
                throw new Exception('订单已处理');
            }
            $rechargeInfo->status = $data[ 'recharge_state' ];
            $rechargeInfo->trade_no = $data[ 'tid' ];
            $rechargeInfo->updated_at = $data[ 'timestamp' ];
            $rechargeInfo->save();
        } catch (Exception $e) {
            Log::debug('banMaNotify-Error:' . $e->getMessage(), [json_encode($data)]);
            throw $e;
        }
    }

    /**
     * 订单充值
     * 付款成功后调用
     *
     * @param int    $order_id 订单ID
     * @param string $order_no 订单编号
     *
     * @return bool
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
        if (strpos($notify_url, 'lk.catspawvideo.com') !== false) {
            $notify_url = str_replace('http://', 'https://', $notify_url);
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
            Log::debug('BanMaMobilePay-Error:' . $e->getMessage(), ['BILL:' . json_encode($PayBill->getBill())]);
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
            $MobileOrder->create_type = '1';
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
