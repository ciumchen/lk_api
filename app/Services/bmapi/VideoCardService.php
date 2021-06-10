<?php

namespace App\Services\bmapi;

use App\Models\Order;
use App\Models\OrderVideo;
use App\Models\TradeOrder;
use Bmapi\Api\VideoCard\VideoItemList;
use Bmapi\Api\VideoCard\VideoRecharge;
use DB;
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
    
    /**
     * 视频会员充值订单生成
     *
     * @param \App\Models\User $user
     * @param float            $money
     * @param string           $project_id
     * @param string           $item_id
     *
     * @return array
     * @throws \Exception
     */
    public function serAllOrder($user, $money, $project_id, $item_id)
    {
        $order_no = createOrderNo();
        DB::beginTransaction();
        try {
            /* 创建 order 表订单*/
            $Order = new Order();
            $Order->setVideoOrder($user->id, $money, $order_no);
            $order_id = $Order->id;
            /* 创建 order_video 表订单*/
            $OrderVideo = new OrderVideo();
            switch ($project_id) {
                case 'c7165':  //优酷
                    $OrderVideo->setYouKuOrder('', '', '', '', '', $item_id);
                    break;
                case 'c7166':
                    //迅雷
                    break;
                case 'c7163':
                    //土豆
                    break;
                case 'c7164':
                    //爱奇艺
                    break;
                case 'c7168':
                    //乐视
                    break;
                case 'c7189':
                    //好莱坞
                    break;
                case 'c7197':
                    //芒果TV移动
                    break;
                case 'c7196':
                    //芒果TV全屏
                    break;
                case 'c7190':
                    //搜狐
                    break;
                case 'c7229':
                    //腾讯
                    break;
                default:
                    throw new Exception('非法提交的充值项目');
            }
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
        DB::commit();
        return $Order->toArray();
    }
    
    /**
     * 支付请求
     *
     * @param string $account
     * @param string $itemId
     * @param string $order_no
     * @param string $notify_url
     *
     * @return array
     * @throws \Exception
     */
    public function billRequest($account, $itemId, $order_no, $notify_url = '')
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
