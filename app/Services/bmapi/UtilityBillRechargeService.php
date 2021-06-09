<?php

namespace App\Services\bmapi;

use Bmapi\Api\UtilityBill\ItemList;
use Bmapi\Api\UtilityBill\ItemPropsList;
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