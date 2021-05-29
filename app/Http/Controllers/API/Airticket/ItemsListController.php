<?php

namespace App\Http\Controllers\API\Airticket;

use App\Http\Controllers\Controller;
use Bmapi\Api\Air\ItemsList;

/** 查询水电煤类标准商品列表 **/

class ItemsListController extends Controller
{
    public function getItems()
    {
        //获取数据
        $ItemList = new ItemsList();
        $res = $ItemList->setPageNo(0)
            ->setPageSize(8)
            ->postParams()
            ->getResult();

        //返回
        return json_decode($res, 1);
    }
}
