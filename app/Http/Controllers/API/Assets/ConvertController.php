<?php

namespace App\Http\Controllers\API\Assets;

use App\Http\Controllers\Controller;
use App\Models\Assets;
use App\Models\ConvertLogs;
use Illuminate\Http\Request;

/** 兑换充值 **/

class ConvertController extends Controller
{
    /**获取用户 usdt 数据
     * @param Request $request
     * @return mixed
     * @throws
     */
    public function getUsdtAmount(Request $request)
    {
        return (new Assets())->getUsdtAmount($request->uid);
    }

    /**usdt 计算兑换金额
     * @param Request $request
     * @return mixed
     * @throws
     */
    public function computePrice(Request $request)
    {
        return (new Assets())->computePrice($request->price);
    }

    /**usdt 兑换话费
     * @param Request $request
     * @return mixed
     * @throws
     */
    public function phoneBill(Request $request)
    {
        $data = $request->all();

        //返回
        return (new Assets())->phoneBill($data);
    }

    /**usdt 兑换美团
     * @param Request $request
     * @return mixed
     * @throws
     */
    public function meituanBill(Request $request)
    {
        $data = $request->all();

        //返回
        return (new Assets())->meituanBill($data);
    }

    /**用户兑换记录列表
     * @param Request $request
     * @return mixed
     * @throws
     */
    public function getConvertList(Request $request)
    {
        $data = $request->all();
        //返回
        return (new ConvertLogs())->getConvertList($data);
    }
}
