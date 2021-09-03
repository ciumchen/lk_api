<?php

namespace App\Http\Controllers\API\Map;

use App\Http\Controllers\Controller;
use Exception;
class BaiduMapApiController extends Controller
{

    //url和key
    public static $AK = "8WXl6Re0R5SFj6cardI0S5joQIblYZ4o";
    public static $ApiUrl = "https://api.map.baidu.com/reverse_geocoding/v3/?";

    public function mapTest()
    {
        $lng= "31.225696563611";
        $lat= "121.49884033194";
        $lng= "22.549063";//113.038451,22.512328
        $lat= "113.942404";
        $data = self::getBaiduMapInfo($lng,$lat);//"latitude":22.549063,"longitude":113.942404,//前端
        print_r($data);

    }

    /*get请求百度地图，通过经纬度获取地址信息
     * $lng:经度,$lat:纬度
     */
    public static function getBaiduMapInfo($lng,$lat){
        $param = [
            'ak'=>self::$AK,
            'output'=>'json',
//            'coordtype'=>'bd09ll',
            'location'=>"{$lat},{$lng}",
        ];
        $params = http_build_query($param);
        $mapData =  json_decode(self::postRequest(self::$ApiUrl.$params),1);
        dd($mapData);
        if ($mapData['status'] != 0){
            return $mapData['status'];//失败
        }else{
            return array(
                'status'=>$mapData['status'],
                'lng'=>$mapData['result']['location']['lng'],
                'lat'=>$mapData['result']['location']['lat'],
                'address'=>$mapData['result']['formatted_address'],
                'country'=>$mapData['result']['addressComponent']['country'],
                'province'=>$mapData['result']['addressComponent']['province'],
                'city'=>$mapData['result']['addressComponent']['city'],
                'district'=>$mapData['result']['addressComponent']['district'],
                'adcode'=>$mapData['result']['addressComponent']['adcode'],
            );

        }
    }

    public static function postRequest($url, $data = [])
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FAILONERROR, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        if (strlen($url) > 5 && strtolower(substr($url, 0, 5)) == "https") {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }
        // POST数据
        if (is_array($data) && count($data)) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        $output = curl_exec($ch);
        if (curl_errno($ch) > 0) {
            throw (new Exception(curl_error($ch)));
        } else {
            $http_status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if (200 !== $http_status_code) {
                throw new Exception($output, $http_status_code);
            }
        }
        curl_close($ch);
        return $output;
    }




}
