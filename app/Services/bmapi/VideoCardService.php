<?php

namespace App\Services\bmapi;

use App\Models\Order;
use App\Models\OrderVideo;
use Bmapi\Api\VideoCard\VideoItemList;
use Bmapi\Api\VideoCard\VideoRecharge;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Log;

class VideoCardService extends BaseService
{
    
    public function updateRechargeLogs($data, $type = '')
    {
        $type = 'VC';
        return parent::updateRechargeLogs($data, $type);
    }
    
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
     * @param string           $account
     * @param float            $money
     * @param string           $project_id
     * @param string           $item_id
     *
     * @return array
     * @throws \Exception|\Throwable
     */
    public function serAllOrder($user, $account, $money, $project_id, $item_id)
    {
        $uid = $user->id;
        $order_no = createOrderNo();
        DB::beginTransaction();
        try {
            /* 创建 order 表订单*/
            $Order = new Order();
            $Order->setVideoOrder($uid, $money, $order_no);
            $order_id = $Order->id;
            /* 创建 order_video 表订单*/
            $OrderVideo = new OrderVideo();
            switch ($project_id) {
                case 'c7165':  //优酷
                    $OrderVideo->setYouKuOrder($account, $money, $order_no, $order_id, $uid, $item_id);
                    break;
                case 'c7166':  //迅雷
                    $OrderVideo->setXunLeiOrder($account, $money, $order_no, $order_id, $uid, $item_id);
                    break;
                case 'c7163':  //土豆
                    $OrderVideo->setTuDouOrder($account, $money, $order_no, $order_id, $uid, $item_id);
                    break;
                case 'c7164':  //爱奇艺
                    $OrderVideo->setIQYiOrder($account, $money, $order_no, $order_id, $uid, $item_id);
                    break;
                case 'c7168':  //乐视
                    $OrderVideo->setLeOrder($account, $money, $order_no, $order_id, $uid, $item_id);
                    break;
                case 'c7189':  //好莱坞
                    $OrderVideo->setHollyWoodOrder($account, $money, $order_no, $order_id, $uid, $item_id);
                    break;
                case 'c7197':  //芒果TV移动
                    $OrderVideo->setMgTVMobileOrder($account, $money, $order_no, $order_id, $uid, $item_id);
                    break;
                case 'c7196':  //芒果TV全屏
                    $OrderVideo->setMgTVFullScreenOrder($account, $money, $order_no, $order_id, $uid, $item_id);
                    break;
                case 'c7190':  //搜狐
                    $OrderVideo->setSoHuOrder($account, $money, $order_no, $order_id, $uid, $item_id);
                    break;
                case 'c7229':  //腾讯
                    $OrderVideo->setTencentOrder($account, $money, $order_no, $order_id, $uid, $item_id);
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
     * 订单充值
     *
     * @param $order_no
     *
     * @return array
     * @throws \Exception
     */
    public function recharge($order_no)
    {
        try {
            /* 视频订单查询 */
            $OrderVideo = new OrderVideo();
            $orderInfo = $OrderVideo->getOrderByOrderNo($order_no);
            if (empty($orderInfo)) {
                throw new Exception('订单信息不存在');
            }
            $bill = $this->billRequest($orderInfo->account, $orderInfo->item_id, $order_no);
            /* 视频订单状态更新 */
            $orderInfo->goods_title = $bill[ 'itemName' ];
            $orderInfo->pay_status = $bill[ 'payState' ];
            $orderInfo->status = $bill[ 'rechargeState' ];
            $orderInfo->trade_no = $bill[ 'billId' ];
            $orderInfo->updated_at = date('Y-m-d H:i:s');
            $orderInfo->save();
        } catch (Exception $e) {
            throw $e;
        }
        return $bill;
    }
    
    /**
     * TODO:更新视频订单
     * 处理回调
     *
     * @param $data
     *
     * @throws \Exception
     */
    public function notify($data)
    {
        $OrderVideo = new OrderVideo();
        try {
            if (empty($data)) {
                throw new Exception('视频会员回调数据为空');
            }
            $VideoRecharge = new VideoRecharge();
            if (!$VideoRecharge->checkSign($data)) {
                throw new Exception('签名校验失败');
            }
            $rechargeInfo = $OrderVideo->where('order_no', '=', $data[ 'outer_tid' ])
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
