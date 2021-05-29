<?php

namespace App\Http\Controllers\API\Airticket;

use App\Http\Controllers\Controller;
use Bmapi\Api\Air\StationsList;

/** 飞机站点信息列表 **/

class StationsListController extends Controller
{
    public function airList()
    {
        //获机场站点信息
        $StationsList = new StationsList();
        $res = $StationsList->setPageNo(0)
            ->setPageSize(8)
            ->postParams()
            ->getResult();

        //获取items
        $ItemsList = new ItemsListController();
        $itemsData = $ItemsList->getItems();
        $items = [];
        foreach ($itemsData as $key => $value)
        {
            foreach ($value as $val)
            {
                $items[] = $val['item'];
            }
        }

        //组装数据
        $stationsData = json_decode($res, 1)['air_stations_list_response']['stations'];
        $stationsData['itemId'] = $items[0][0]['itemId'];

        //返回
        return $stationsData;
    }
}
