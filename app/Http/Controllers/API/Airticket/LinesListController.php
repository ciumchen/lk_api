<?php

namespace App\Http\Controllers\API\Airticket;

use App\Http\Controllers\Controller;
use Bmapi\Api\Air\LinesList;
use Illuminate\Http\Request;

/** 航线列表 **/

class LinesListController extends Controller
{
    /**获取航线列表
     * @param Request $request
     * @return mixed
     * @throws
     */
    public function linesList(Request $request)
    {
        //获取数据
        $LinesList = new LinesList();
        $res = $LinesList->setFrom($request->from)
            ->setTo($request->to)
            ->setDate($request->date)
            ->setItemId($request->itemId)
            ->postParams()
            ->getResult();

        //返回
        return json_decode($res, 1);
    }
}
