<?php

namespace App\Services\bmapi;

use App\Models\Order;
use App\Models\OrderUtilityBill;
use App\Models\TradeOrder;
use App\Models\User;
use Bmapi\Api\UtilityBill\GetAccountInfo;
use Bmapi\Api\UtilityBill\ItemList;
use Bmapi\Api\UtilityBill\ItemPropsList;
use Bmapi\Api\UtilityBill\LifeRecharge;
use DB;
use Exception;

/**
 * Class UtilityBillRechargeService
 *
 * @package App\Services\bmapi
 */
class UtilityBillRechargeService extends BaseService
{
    
    /**
     * 水电煤气查询
     *
     * @param string $project_id
     * @param string $city
     * @param int    $page
     * @param int    $page_size
     *
     * @return array
     * @throws \Exception
     */
    public function searchList($project_id, $city, $page = 0, $page_size = 10)
    {
        $city = trim($city, '市');
        try {
            $ItemList = new ItemList();
            $ItemList->setPageNo($page)
                     ->setPageSize($page_size)
                     ->setCity($city)
                     ->setProjectId($project_id)
                     ->getResult();
            $res = $ItemList->getList();
        } catch (Exception $e) {
            throw $e;
        }
        return $res;
    }
    
    /**
     * 水电煤查账
     *
     * @param string $item_id
     * @param string $account
     * @param string $project_id
     *
     * @return array
     * @throws \Exception
     */
    public function checkBill($item_id, $account, $project_id)
    {
        try {
            /* 商品属性查询 */
            $next_params = $this->getGoodsList($item_id);
            /* 查账单信息 */
            $info = $this->getInfo($item_id, $account, $project_id, $next_params);
        } catch (Exception $e) {
            throw $e;
        }
        return $info;
    }
    
    /**
     * Description:订单充值
     *
     * @param int                    $order_id
     * @param \App\Models\Order|null $Order
     *
     * @throws \Exception
     * @author lidong<947714443@qq.com>
     * @date   2021/6/15 0015
     */
    public function recharge($order_id, Order $Order = null)
    {
        if (empty($Order)) {
            $Order = Order::find($order_id);
        }
        try {
            if (empty($Order)) {
                throw new Exception('订单不存在');
            }
            if (empty($Order->utility)) {
                throw new Exception('生活缴费数据不存在');
            }
            if ($Order->utility->pay_status != '0') {
                throw new Exception('订单 ' . $Order->order_no . ' 无法充值');
            }
            $bill = $this->billRecharge(
                $Order->utility->account,
                $Order->utility->item_id,
                $Order->utility->money,
                $Order->utility->bill_cycle,
                $Order->utility->contract_no,
                $Order->utility->content_id,
                $Order->utility->item4,
            );
        } catch (Exception $e) {
            throw $e;
        }
    }
    
    /**
     * 账单充值
     * 付款后
     *
     * @param string $account    充值账号
     * @param string $itemId     标准商品ID
     * @param string $money      充值金额
     * @param string $billCycle  账单账期
     * @param string $contractNo 合同号
     * @param string $contentId  账期标识
     * @param string $item4      扩展字段
     *
     * @return array|null
     * @throws \Exception
     */
    public function billRecharge(
        $account,
        $itemId,
        $money,
        $billCycle = '',
        $contractNo = '',
        $contentId = '',
        $item4 = ''
    ) {
        try {
            $LIfeRecharge = new LifeRecharge();
            $LIfeRecharge->setItemId($itemId)
                         ->setItemNum($money)
                         ->setRechargeAccount($account);
            if ($billCycle) {
                $LIfeRecharge->setBillCycle($billCycle);
            }
            if ($contractNo) {
                $LIfeRecharge->setContractNo($contractNo);
            }
            if ($contentId) {
                $LIfeRecharge->setContentId($contentId);
            }
            if ($item4) {
                $LIfeRecharge->setItem4($item4);
            }
            $LIfeRecharge->getResult();
            $data = $LIfeRecharge->getData();
        } catch (Exception $e) {
            throw $e;
        }
        return $data;
    }
    
    /**
     * Description: 生成订单
     *
     * @param \App\Models\User $user
     * @param                  $account
     * @param                  $project_id
     * @param                  $itemId
     * @param                  $money
     * @param string           $billCycle
     * @param string           $contractNo
     * @param string           $contentId
     * @param string           $item4
     *
     * @return \App\Models\Order
     * @throws \Throwable
     * @author lidong<947714443@qq.com>
     * @date   2021/6/15 0015
     */
    public function setUtilityOrder(
        User $user,
        $account,
        $project_id,
        $itemId,
        $money,
        $billCycle = '',
        $contractNo = '',
        $contentId = '',
        $item4 = ''
    ) {
        DB::beginTransaction();
        try {
            $orderNo = createOrderNo();
            $Order = new Order();
            $UtilityOrder = new OrderUtilityBill();
            switch ($project_id) {
                case 'c2670': /* 水费订单 */
                    /* 生成Order订单*/
                    $orderInfo = $Order->setWaterOrder($user->id, $money, $orderNo);
                    /*生成缴费订单*/
                    $UtilityOrder->setWaterOrder(
                        $account,
                        $money,
                        $orderNo,
                        $orderInfo->id,
                        $user->id,
                        $itemId,
                        $billCycle,
                        $contractNo,
                        $contentId,
                        $item4
                    );
                    break;
                case 'c2680': /* 电费订单 */
                    $orderInfo = $Order->setElectricityOrder($user->id, $money, $orderNo);
                    $UtilityOrder->setElectricityOrder(
                        $account,
                        $money,
                        $orderNo,
                        $orderInfo->id,
                        $user->id,
                        $itemId,
                        $billCycle,
                        $contractNo,
                        $contentId,
                        $item4
                    );
                    break;
                case 'c2681': /* 燃气费订单 */
                    $orderInfo = $Order->setGasOrder($user->id, $money, $orderNo);
                    $UtilityOrder->setGasOrder(
                        $account,
                        $money,
                        $orderNo,
                        $orderInfo->id,
                        $user->id,
                        $itemId,
                        $billCycle,
                        $contractNo,
                        $contentId,
                        $item4
                    );
                    break;
                default:
                    throw new Exception('订单类型不在范围内');
            }
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
        return $orderInfo;
    }
    
    /**
     * 查询账单信息
     *
     * @param string $item_id    标准商品编号
     * @param string $account    缴费单标识号
     * @param string $project_id 缴费项目编号，水费c2670，电费c2680，气费c2681
     * @param array  $data       [
     *                           'province',
     *                           'mode_id',
     *                           'city',
     *                           'city_id',
     *                           'unit_id',
     *                           'unit_name',
     *                           ]
     *
     * @return array
     * @throws \Exception
     */
    public function getInfo($item_id, $account, $project_id, $data)
    {
        $GetAccountInfo = new GetAccountInfo();
        try {
            $GetAccountInfo->setItemId($item_id)           // 标准商品编号(页面选择)
                           ->setAccount($account)          // 缴费单标识号（户号或条形码）
                           ->setCity($data[ 'city' ])      // 市名称(后面不带"市"，属性查询接口中返回的参数itemProps-"type": "CITYIN"下的vname)
                           ->setCityId($data[ 'city_id' ]) // 城市V编号(属性查询接口中返回的参数itemProps-"type": "CITYIN"下的vid)
                           ->setModeId($data[ 'mode_id' ]) // 缴费方式V编号 (属性查询接口中返回的参数itemProps-"type": "SPECIAL"下的vid)
                           ->setModeType(2)            // 缴费方式：1是条形码 2是户号
                           ->setProjectId($project_id)        // 缴费项目编号，水费c2670，电费c2680，气费c2681，(属性查询接口中返回的参数cid)
                           ->setProvince($data[ 'province' ]) //省名称 属性查询接口中返回的参数itemProps-"type":"PRVCIN"下的vname
                           ->setUnitId($data[ 'unit_id' ])     // 缴费单位V编号(属性查询接口中返回的参数itemProps-"type": "BRAND"下的vid)
                           ->setUnitName($data[ 'unit_name' ]) // 缴费单位名称(属性查询接口中返回的参数itemProps-"type": "BRAND"下的vname)
                           ->getResult();
            $res = $GetAccountInfo->getBill();
            $status = $GetAccountInfo->getStatus();
            $msg = $GetAccountInfo->getMessage();
            if ($status == 0) {
                throw new Exception($msg);
            }
        } catch (Exception $e) {
            throw $e;
        }
        return $res;
    }
    
    /**
     * 商品属性查询
     *
     * @param $item_id
     *
     * @return array
     * @throws \Exception
     */
    public function getGoodsList($item_id)
    {
        $ItemPropsList = new ItemPropsList();
        try {
            $ItemPropsList->setItemId($item_id)
                          ->getResult();
            $res = $ItemPropsList->getList();
            $next_params = $ItemPropsList->getNextParams();
            if (empty($next_params)) {
                throw new Exception('没有查询到对应的数据');
            }
        } catch (\Exception $e) {
            throw $e;
        }
        return $next_params;
    }
}
