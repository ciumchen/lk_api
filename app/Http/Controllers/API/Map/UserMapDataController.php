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
                $cityDataInfo = CityData::where('code', $userCity['adcode'])->first();
                if (empty($cityDataInfo)) {
                    return false;
                }
                $sqArr = explode(',',$cityDataInfo->pid_route);
                //保存用户经纬度信息
                isset($sqArr[0])?:$sqArr[0]=0;
                isset($sqArr[1])?:$sqArr[1]=0;
                $data = [
                    'uid' => $uid,
                    'province_id' => $sqArr[0],
                    'city_id' => $sqArr[1],
                    'district_id' => $cityDataInfo->id,
                    'address' => $userCity['address'],
                    'lng' => $userCity['lng'],
                    'lat' => $userCity['lat'],
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
