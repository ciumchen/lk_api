<?php

namespace App\Services\bmapi;

use Bmapi\Api\VideoCard\VideoItemList;
use Bmapi\Api\VideoCard\VideoRecharge;
use Exception;

class VideoCardService
{
    
    /**
     * 查询可用商品
     *
     * @param string $projectId
     * @param string $itemId
     * @param string $itemName
     * @param string $pageNo
     * @param string $pageSize
     *
     * @return array
     * @throws \Exception
     */
    public function getList($projectId = '', $itemId = '', $itemName = '', $pageNo = '', $pageSize = '')
    {
        $VideoList = new VideoItemList();
        if ($projectId) {
            $VideoList->setProjectId($projectId);
        }
        if ($itemId) {
            $VideoList->setItemId($itemId);
        }
        if ($itemName) {
            $VideoList->setItemName($itemName);
        }
        if ($pageNo) {
            $VideoList->setPageNo($projectId);
        }
        if ($pageSize) {
            $VideoList->setPageSize($projectId);
        }
        try {
            $VideoList->getResult();
        } catch (Exception $e) {
            throw $e;
        }
        return $VideoList->getLists();
    }
    
    public function recharge($account, $itemId, $order_no, $notify_url = '')
    {
        if (empty($notify_url)) {
            $notify_url = url('/api/video-notify');
        }
        if (strpos($notify_url, 'lk.catspawvideo.com') !== false) {
            $notify_url = str_replace('http://', 'https://', $notify_url);
        }
        try {
            $VideoRecharge = new VideoRecharge();
            $VideoRecharge->setAccount($account)
                          ->setItemId($itemId)
                          ->setOuterTid($order_no)
                          ->setCallback($notify_url)
                          ->getResult();
        } catch (Exception $e) {
            throw $e;
        }
        return $VideoRecharge->getBill();
    }
}
