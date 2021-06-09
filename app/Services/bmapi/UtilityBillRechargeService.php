<?php

namespace App\Services\bmapi;

use App\Models\TradeOrder;
use Bmapi\Api\UtilityBill\GetAccountInfo;
use Bmapi\Api\UtilityBill\ItemList;
use Bmapi\Api\UtilityBill\ItemPropsList;
use Bmapi\Api\UtilityBill\LifeRecharge;
use Exception;

/**
 * Class UtilityBillRechargeService
 *
 * @package App\Services\bmapi
 */
class UtilityBillRechargeService
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
            $ItemList->setPageNo(0)
                     ->setPageSize(10)
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
     * @param $item_id
     * @param $account
     * @param $project_id
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
     * 账单充值
     *
     * @param string $account
     * @param string $itemId
     * @param string $money
     *
     * @throws \Exception
     */
    public function billRecharge($account, $itemId, $money)
    {
        /* TODO:账单充值 */
        try {
            $LIfeRecharge = new LifeRecharge();
            $LIfeRecharge->setItemId($itemId)
                         ->setItemNum($money)
                         ->setRechargeAccount($account)
                         ->getResult();
            $data = $LIfeRecharge->getData();
            $TradeOrder = new TradeOrder();
            $order_no = $TradeOrder->CreateOrderNo();
            /* TODO:生成订单 */
            
            /* TODO:生成水电费订单 */
        } catch (Exception $e) {
            throw $e;
        }
    }
    
    public function createUtilityOrder()
    {
        //生成订单
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
//            $status = $GetAccountInfo->getStatus();
//            $msg = $GetAccountInfo->getMessage();
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
