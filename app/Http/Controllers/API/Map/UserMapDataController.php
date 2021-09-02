<?php

namespace App\Http\Controllers\API\Map;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
class UserMapDataController extends Controller
{

    //通过经纬度获取用户的省市区地址
    public function addUserCityAddr(Request $request)
    {
        $lng= $request->input('lng');
        $lat= $request->input('lat');
        $uid= $request->input('uid');
        $userCity = BaiduMapApiController::getBaiduMapInfo($lng,$lat);
        if ($userCity['status']==0){
            dd($userCity);
        }

        dd($userCity);

    }



}
