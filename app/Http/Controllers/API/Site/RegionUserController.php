<?php

namespace App\Http\Controllers\API\Site;

use App\Http\Controllers\Controller;
use App\Models\RegionUser;
use Illuminate\Http\Request;

/** 区域代理 **/

class RegionUserController extends Controller
{
    /**获取用户区域代理信息
     * @param Request $request
     * @return mixed
     * @throws
     */
    public function getNode(Request $request)
    {
        return (new RegionUser())->getNode($request->uid);
    }

    /**获取市级代理信息
     * @param Request $request
     * @return mixed
     * @throws
     */
    public function getCity(Request $request)
    {
        return (new RegionUser())->getCityNode($request->code, $request->page, $request->perPage);
    }

    /**获取区级代理信息
     * @param Request $request
     * @return mixed
     * @throws
     */
    public function getDistrict(Request $request)
    {
        return (new RegionUser())->getDistrictNode($request->code, $request->page, $request->perPage);
    }

    /**获取市级代理资产积分记录
     * @param Request $request
     * @return mixed
     * @throws
     */
    public function getCityAssets(Request $request)
    {
        return (new RegionUser())->getCityAssets($request->code, $request->page, $request->perPage);
    }

    /**获取区级代理资产积分记录
     * @param Request $request
     * @return mixed
     * @throws
     */
    public function getAssets(Request $request)
    {
        return (new RegionUser())->getAssets($request->code, $request->page, $request->perPage);
    }

    /**获取区级代理商家录单让利订单列表
     * @param Request $request
     * @return mixed
     * @throws
     */
    public function getProfitAmount(Request $request)
    {
        $data = $request->all();
        return (new RegionUser())->getProfitAmount($data);
    }
}
