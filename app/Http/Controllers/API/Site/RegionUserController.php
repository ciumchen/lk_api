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
        return (new RegionUser())->getCityNode($request->code);
    }

    /**获取区级代理信息
     * @param Request $request
     * @return mixed
     * @throws
     */
    public function getDistrict(Request $request)
    {
        return (new RegionUser())->getDistrictNode($request->district, $request->page, $request->perPage);
    }
}
