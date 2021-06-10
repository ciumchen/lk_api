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
        $VideoServices = new VideoCardService();
        try {
            $list = $VideoServices->getList($projectId, $itemId, $itemName, $pageNo, $pageSize);
        } catch (Exception $e) {
            throw new LogicException($e->getMessage());
        }
        return apiSuccess($list);
    }
    
    public function rechargeTest(Request $request)
    {
        $account = $request->input('');
        $itemId = $request->input('');
        $order_no = $request->input('');
        $VideoServices = new VideoCardService();
        try {
            $bill = $VideoServices->billRequest($account, $itemId, $order_no);
        } catch (Exception $e) {
            throw new LogicException($e->getMessage());
        }
        return apiSuccess($bill);
    }
}
