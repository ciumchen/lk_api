<?php

namespace App\Http\Controllers\API\Map;

use App\Exceptions\LogicException;
use App\Http\Controllers\Controller;
use App\Models\CityData;
use App\Models\UserCityData;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class UserMapDataController extends Controller
{

    //通过经纬度获取用户的省市区地址
    public function addUserCityAddr(Request $request)
    {
        $lng = $request->input('lng');
        $lat = $request->input('lat');
        $uid = $request->input('uid');

        //判断该用户是否已经上传过经纬度
        $reData = UserCityData::where('uid', $uid)->first();
        if ($reData) {
            return response()->json(['code' => 0, 'data' => $reData, 'msg' => '您已完成经纬度上传，不能再次上传']);
        }

        //上传经纬度
        $userCity = BaiduMapApiController::getBaiduMapInfo($lng, $lat);
        if ($userCity['status'] != 0) {
            throw new LogicException('上传经纬度失败', '500');
        } else {
            DB::beginTransaction();
            try {
                $cityDataId = CityData::where('code', $userCity['adcode'])->value('id');
                if (empty($cityDataId)) {
                    return false;
                }
                //保存用户经纬度信息
                $data = [
                    'uid' => $uid,
                    'province' => $userCity['province'],
                    'city' => $userCity['city'],
                    'district' => $userCity['district'],
                    'address' => $userCity['address'],
                    'lng' => $lng,
                    'lat' => $lat,
                    'city_data_id' => $cityDataId,
                ];
                (new UserCityData())->addUserCityData($data);

            } catch (Exception $e) {
                DB::rollBack();
                throw new LogicException($e->getMessage());
            }
            DB::commit();
            return response()->json(['code' => 0, 'data' => $data, 'msg' => '您已成功上传经纬度信息']);

        }


    }


}
