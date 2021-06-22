<?php

namespace App\Services;

use App\Exceptions\LogicException;
use App\Http\Controllers\API\Airticket\OrderPayBillController;
use App\Models\AirTradeLogs;
use App\Models\BusinessData;
use App\Models\CityData;
use App\Models\CityNode;
use App\Models\Order;
use App\Models\OrderAirTrade;
use App\Models\RechargeLogs;
use Illuminate\Support\Facades\DB;

class RegionUserService
{
    /**判断用户为哪一级区域代理
    * @param string $uid
    * @return mixed
    * @throws
    */
    public function isRegion(string $uid)
    {
        //获取用户数据
        $nodeInfo = CityNode::where('uid', $uid)->get(['province', 'city', 'district'])->first();
        if (isset($nodeInfo->district))
        {
            $data = [
                'node' => 'district',
                'code' => $nodeInfo->district
            ];
        } elseif (isset($nodeInfo->city) && !isset($nodeInfo->district))
        {
            $data = [
                'node' => 'city',
                'code' => $nodeInfo->city
            ];
        } else
        {
            $data = [
                'node' => 'province',
                'code' => $nodeInfo->province
            ];
        }

        //返回
        return $data;
    }

    /**获取市级区域代理信息
     * @param string $code
     * @return mixed
     * @throws
     */
    public function getCityNode(string $code)
    {
        //市级区域信息
        $cityInfo = CityNode::where('city', $code)
            ->whereRaw('district is null')
            ->get(['uid', 'name', 'created_at'])->first();

        //获取属于市级区域商家总数
        $businessSum = BusinessData::where(['city' => $code, 'status' => 1])->count();

        //获取每个区级商家总数
        $cityData = CityNode::where('city', $code)->whereRaw('district is not null')->get(['district']);
        $districtDict = array_column(json_decode($cityData, 1), 'district');
        $districtData = BusinessData::select('district', DB::raw('count(*) as shopTotal'))
            ->whereIn('district', $districtDict)
            ->groupBy('district')
            ->orderBy('district', 'asc')
            ->get();
        $districtArr = json_decode($districtData, 1);

        //获取区级地理信息
        $districtNames = CityData::whereIn('code', $districtDict)->get(['code', 'name']);
        $nameDict = array_column(json_decode($districtNames, 1), null, 'code');

        //获取每个区级地区的商家uid
        $disuserData = BusinessData::whereIn('district', $districtDict)
            ->orderBy('district', 'asc')
            ->get(['district', 'uid']);
        $disuserDict = json_decode($disuserData, 1);

        //合并同一区级商家 uid
        $disuserRes = [];
        foreach ($disuserDict as $k => $v)
        {
            $disuserRes[$v['district']][] = $v['uid'];
        }

        //当前区级商家录单总的让利金额
        $integralSum= [];
        foreach ($disuserRes as $key => $value)
        {
            $priceTotal = Order::select(DB::raw('sum(profit_price) as total'))
                ->whereIn('business_uid', $value)
                ->where(['status' => 2, 'name' => '录单'])
                ->where('updated_at', '>=', $cityInfo->created_at)
                ->get();
            $integralSum[$key]['priceTotal'] = json_decode($priceTotal, 1)[0]['total'];
        }

        //组装区级商家数据
        foreach ($districtArr as $key => $val)
        {
            $districtArr[$key]['name'] = $nameDict[$val['district']]['name'];
            $districtArr[$key]['priceTotal'] = $integralSum[$val['district']]['priceTotal'];
        }

        //组装最后返回数据
        $cityArr['businessList'] = $districtArr;
        $cityArr['inteTotal'] = sprintf('%.2f',array_sum(array_column($districtArr, 'priceTotal')) * 0.0125);
        $cityArr['region'] = $cityInfo->name;
        $cityArr['businessSum'] = $businessSum;

        //返回
        return $cityArr;
    }

    /**获取市级区域代理信息
     * @param string $code
     * @param string $page
     * @param string $perPage
     * @return mixed
     * @throws
     */
    public function getDistrictNode(string $code, int $page, int $perPage)
    {
        $districtInfo = CityNode::where('district', $code)->get(['uid', 'name', 'created_at'])->first();

        //获取属于市级区域商家总数
        $businessSum = BusinessData::where(['district' => $code, 'status' => 1])->count();

        //区级商家信息
        $businessList = BusinessData::where('district', $code)
            ->orderBy('uid', 'asc')
            ->get(['uid', 'name', 'contact_number']);

        $businessData = json_decode($businessList, 1);

        $businessUids = array_column($businessData, 'uid');

        //获取商家让利金额总和
        $priceTotal = Order::select(DB::raw('business_uid as uid, sum(profit_price) as total'))
            ->whereIn('business_uid', $businessUids)
            ->where(['status' => 2, 'name' => '录单'])
            ->where('updated_at', '>=', $districtInfo->created_at)
            ->groupBy('business_uid')
            ->get();

        $priceData = array_column(json_decode($priceTotal, 1), null, 'uid');

        foreach ($businessData as $key => $val)
        {
            $businessData[$key]['integralTotal'] = $priceData[$val['uid']]['total'] ?? 0;
        }

        //分页
        $start = ($page - 1) * $perPage;
        $length = $perPage;

        $businessArr['businessList'] = array_slice($businessData, $start, $length);
        $businessArr['region'] = $districtInfo->name;
        $businessArr['businessSum'] = $businessSum;
        $businessArr['priceTotal'] = sprintf('%.2f',array_sum(array_column($businessData, 'integralTotal')) * 0.0175);

        //返回
        return $businessArr;
    }

}
