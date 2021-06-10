<?php

namespace App\Http\Controllers\API\Shop;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use Illuminate\Http\Request;

/* 获取商家积分 */

class GetIntegralController extends Controller
{
    /**获取积分记录
     * @param Request $request
     * @return mixed
     * @throws
     */
    public function logsList(Request $request)
    {
        $data = $request->all();
        return (new Shop())->logsList($data);
    }

    /**获取排队积分记录
     * @param Request $request
     * @return mixed
     * @throws
     */
    public function lineList(Request $request)
    {
        $data = $request->all();
        return (new Shop())->lineList($data);
    }
}
