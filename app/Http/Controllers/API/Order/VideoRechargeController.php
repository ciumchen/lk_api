<?php

namespace App\Http\Controllers\API\Order;

use App\Exceptions\LogicException;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\bmapi\VideoCardService;
use App\Services\ShowApi\VideoOrderService;
use Exception;
use Illuminate\Http\Request;

class VideoRechargeController extends Controller
{
    
    //
    
    /**
     * 查询可重置项
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return string
     * @throws \App\Exceptions\LogicException
     */
    public function getVideoList(Request $request)
    {
        $projectId = $request->input('project_id', '');
        $itemId = $request->input('item_id', '');
        $itemName = $request->input('item_name', '');
        $pageNo = $request->input('page', '');
        $pageSize = $request->input('page_size', '');
        $VideoService = new VideoCardService();
        try {
            $list = $VideoService->getList($projectId, $itemId, $itemName, $pageNo, $pageSize);
        } catch (Exception $e) {
            throw new LogicException($e->getMessage());
        }
        return apiSuccess($list);
    }
    
    /**
     * 视频会员订单
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return string
     * @throws \App\Exceptions\LogicException|\Throwable
     */
    public function setOrder(Request $request)
    {
        $user = $request->user();
        $account = $request->input('account');
        $money = $request->input('money');
        $project_id = $request->input('project_id');
        $item_id = $request->input('item_id');
        $VideService = new VideoCardService();
        try {
            $order = $VideService->serAllOrder($user, $account, $money, $project_id, $item_id);
        } catch (Exception $e) {
            throw new LogicException($e->getMessage());
        }
        return apiSuccess($order);
    }
    
    /**
     * 视频充值测试
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return string
     * @throws \App\Exceptions\LogicException
     */
    public function rechargeTest(Request $request)
    {
        $account = $request->input('account');
        $itemId = $request->input('item_id');
        $order_no = $request->input('order_no');
        $VideoService = new VideoCardService();
        try {
            $bill = $VideoService->billRequest($account, $itemId, $order_no);
        } catch (Exception $e) {
            throw new LogicException($e->getMessage());
        }
        return apiSuccess($bill);
    }
    
    /**
     * Description:万维视频列表接口
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return string
     * @throws \App\Exceptions\LogicException
     * @author lidong<947714443@qq.com>
     * @date   2021/6/22 0022
     */
    public function getWanWeiVideoList(Request $request)
    {
        try {
            $VideoService = new VideoOrderService();
            $list = $VideoService->getList();
        } catch (\Exception $e) {
            throw new LogicException($e->getMessage());
        }
        return apiSuccess($list);
    }
    
    /**
     * Description:万维视频卡密获取[充值下单]
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return string
     * @throws \App\Exceptions\LogicException
     * @author lidong<947714443@qq.com>
     * @date   2021/6/22 0022
     */
    public function rechargeWanWeiTest(Request $request)
    {
        $order_id = $request->input('order_id');
        try {
            $Order = Order::find($order_id);
            $VideoService = new VideoOrderService();
            $card_list = $VideoService->recharge($order_id, $Order);
        } catch (Exception $e) {
            throw new LogicException($e->getMessage());
        }
        return apiSuccess($card_list);
    }
    
    /**
     * Description:万维视频订单生成
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return string
     * @throws \App\Exceptions\LogicException
     * @throws \Throwable
     * @author lidong<947714443@qq.com>
     * @date   2021/6/22 0022
     */
    public function setWanWeiVideoOrder(Request $request)
    {
        $genusId = $request->input('genus_id', '');
        $money = $request->input('money', 0);
        if (empty($genusId)) {
            throw new LogicException('请选择需要购买的会员');
        }
        if ($money <= 0) {
            throw new LogicException('支付金额异常');
        }
        $user = $request->user();
        try {
            $VideoService = new VideoOrderService();
            $info = $VideoService->setOrder($user, $money, $genusId);
        } catch (Exception $e) {
            throw new LogicException($e->getMessage());
        }
        return apiSuccess($info);
    }
}
