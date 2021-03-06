<?php

namespace App\Http\Controllers\API\Airticket;

use App\Exceptions\LogicException;
use App\Http\Controllers\Controller;
use Bmapi\Api\Air\LinesList;
use Illuminate\Http\Request;

/** 查询航线列表 **/

class LinesListController extends Controller
{
    /**获取航线列表
     * @param Request $request
     * @return mixed
     * @throws
     */
    public function linesList(Request $request)
    {
        //检查日期数据
        if (strtotime($request->date) < time() - 86400)
        {
            throw new LogicException('日期必须大于当前日期');
        }

        //获取数据
        $linesList = new LinesList();
        $linesInfo = $linesList->setFrom($request->from)
            ->setTo($request->to)
            ->setDate($request->date)
            ->setItemId($request->itemId)
            ->postParams()
            ->getResult();

        $linesArr = json_decode($linesInfo, 1);

        //返回
        return $linesArr['airlines_list_response']['airlines']['airline'];
    }
}
