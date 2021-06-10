<?php

namespace App\Http\Controllers\API\Order;

use App\Exceptions\LogicException;
use App\Http\Controllers\Controller;
use App\Services\bmapi\VideoCardService;
use Exception;
use Illuminate\Http\Request;

class VideoRechargeController extends Controller
{
    
    //
    
    /**
     * 查询可重置项
     *
     * @param \Illuminate\Http\Request $request
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
     * @param \Illuminate\Http\Request $request
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
     * @param \Illuminate\Http\Request $request
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
}
