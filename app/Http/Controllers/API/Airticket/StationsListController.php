<?php

namespace App\Http\Controllers\API\Airticket;

use App\Http\Controllers\Controller;
use Bmapi\Api\Air\StationsList;
use Bmapi\Api\Air\ItemsList;

/** 飞机站点信息列表 **/

class StationsListController extends Controller
{
    public function airList()
    {
        //获机场站点信息
        $stationsList = new StationsList();
        $res = $stationsList
            ->setPageNo(0)
            ->setPageSize(10)
            ->postParams()
            ->getResult();

        //获取items
        $itemsData = $this->getItems();
        $items = [];
        foreach ($itemsData as $value)
        {
            foreach ($value as $val)
            {
                var_dump($val['item']);
                $items[] = $val['item'];
            }
        }
        dd($items);

        //组装数据
        $stationsData = json_decode($res, 1)['air_stations_list_response']['stations'];
        $stationsData['itemId'] = $items[0][0]['itemId'];

        //返回
        return $stationsData;
    }

    //查询飞机票标准商品列表
    public function getItems()
    {
        //获取数据
        $itemList = new ItemsList();
        $res = $itemList->setPageNo(0)
            ->setPageSize(10)
            ->postParams()
            ->getResult();

        //返回
        return json_decode($res, 1);
    }
}
