<?php

namespace App\Http\Controllers\API\Airticket;

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
        //获取数据
        $linesList = new LinesList();
        $linesInfo = $linesList->setFrom($request->from)
            ->setTo($request->to)
            ->setDate($request->date)
            ->setItemId($request->itemId)
            ->postParams()
            ->getResult();
        /* $linesInfo = $linesList
        ->setFrom('CKG')
        ->setTo('SZX')
        ->setDate('2021-06-16')
        ->setItemId('5500301')
        ->postParams()
        ->getResult(); */

        $linesArr = json_decode($linesInfo, 1);

        //返回
        return $linesArr['airlines_list_response']['airlines']['airline'];
    }
}
