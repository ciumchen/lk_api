<?php

namespace App\Services;

use App\Exceptions\LogicException;
use App\Models\AssetsLogs;
use App\Models\BusinessData;
use App\Models\CityData;
use App\Models\CityNode;
use App\Models\Order;
use App\Models\RegionUser;
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
     * @param int $page
     * @param int $perPage
     * @return mixed
     * @throws
     */
    public function getCityNode(string $code, int $page, int $perPage)
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
        $disuserData = BusinessData::where('city', $code)
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
            $districtArr[$key]['priceTotal'] = sprintf('%.2f', $integralSum[$val['district']]['priceTotal'] * 0.0175);
        }

        //分页
        $start = ($page - 1) * $perPage;
        $length = $perPage;

        //组装数据
        $cityArr['businessList'] = array_slice($districtArr, $start, $length);
        $cityArr['inteTotal'] = sprintf('%.2f', array_sum(array_column($integralSum, 'priceTotal')) * 0.0125);
        $cityArr['region'] = $cityInfo->name;
        $cityArr['businessSum'] = $businessSum;

        //返回
        return $cityArr;
    }

    /**获取市级区域代理信息
     * @param string $code
     * @param int $page
     * @param int $perPage
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
            $businessData[$key]['priceTotal'] = sprintf('%.2f', ($priceData[$val['uid']]['total'] ?? 0) * 0.0175);
        }

        //分页
        $start = ($page - 1) * $perPage;
        $length = $perPage;

        //按积分排序
        array_multisort(array_column($businessData, 'priceTotal'), SORT_DESC, $businessData);
        $businessArr['businessList'] = array_slice($businessData, $start, $length);
        $businessArr['region'] = $districtInfo->name;
        $businessArr['businessSum'] = $businessSum;
        $businessArr['inteTotal'] = sprintf('%.2f', array_sum(array_column($businessData, 'priceTotal')));

        //返回
        return $businessArr;
    }

    /**获取市级区域代理录单积分记录
     * @param $cityInfo
     * @param int $page
     * @param int $perPage
     * @return mixed
     * @throws
     */
    public function getCityAssets($cityInfo, int $page, int $perPage)
    {
        $time = $cityInfo->created_at;
        $uid = $cityInfo->uid;

        $res = AssetsLogs::where('uid', $uid)->exists();
        if (!$res)
        {
            throw new LogicException('该市级代理资产记录不存在');
        }

        //获取资产列表
        $assetsList = DB::table('assets_logs')
                        ->where(['uid' => $uid, 'operate_type' => 'city_rebate', 'remark' => '市节点运营返佣'])
                        ->where('created_at', '>=', $time)
                        ->orderBy('created_at', 'desc')
                        ->forPage($page, $perPage)
                        ->get(['amount', 'created_at']);

        $assetsData = json_decode($assetsList, 1);

        //总金额
        $amountSum = DB::table('assets_logs')
                        ->where(['uid' => $uid, 'operate_type' => 'city_rebate', 'remark' => '市节点运营返佣'])
                        ->where('created_at', '>=', $time)
                        ->sum('amount');

        //组装数据
        foreach ($assetsData as $key => $val)
        {
            $assetsData[$key]['amount'] = sprintf('%.2f', $val['amount']);
            $assetsData[$key]['name'] = '录单';
        }
        $assetsArr['assetsData'] = $assetsData;
        $assetsArr['amountSum'] = sprintf('%.2f', $amountSum);

        //返回
        return $assetsArr;
    }

    /**获取区级区域代理录单积分记录
     * @param $districtInfo
     * @param int $page
     * @param int $perPage
     * @return mixed
     * @throws
     */
    public function getAssets($districtInfo, int $page, int $perPage)
    {
        $uid = $districtInfo->uid;
        $time = $districtInfo->created_at;
        $res = AssetsLogs::where('uid', $uid)->exists();
        if (!$res)
        {
            throw new LogicException('该区级代理资产记录不存在');
        }

        //获取资产列表
        $assetsList = DB::table('assets_logs')
                        ->where(['uid' => $uid, 'operate_type' => 'district_rebate', 'remark' => '区级节点运营返佣'])
                        ->where('created_at', '>=', $time)
                        ->orderBy('created_at', 'desc')
                        ->forPage($page, $perPage)
                        ->get(['amount', 'created_at']);

        $assetsData = json_decode($assetsList, 1);

        //总金额
        $amountSum = DB::table('assets_logs')
                        ->where(['uid' => $uid, 'operate_type' => 'district_rebate', 'remark' => '区级节点运营返佣'])
                        ->where('created_at', '>=', $time)
                        ->sum('amount');

        //组装数据
        foreach ($assetsData as $key => $val)
        {
            $assetsData[$key]['amount'] = sprintf('%.2f', $val['amount']);
            $assetsData[$key]['name'] = '录单';
        }
        $assetsArr['assetsData'] = $assetsData;
        $assetsArr['amountSum'] = sprintf('%.2f', $amountSum);

        //返回
        return $assetsArr;
    }

    /**获取区级代理商家录单让利订单列表
     * @param array $data
     * @return mixed
     * @throws
     */
    public function getProfitAmount(array $data)
    {
        //订单列表
        $orderInfo = Order::where(['business_uid' => $data['uid'], 'status' => 2, 'name' => '录单'])
                        ->where('updated_at', '>=', $data['time'])
                        ->orderBy('created_at', 'desc')
                        ->forPage($data['page'], $data['perPage'])
                        ->get(['name', 'profit_ratio', 'profit_price', 'created_at']);
        $orderList = json_decode($orderInfo, 1);

        //订单总数
        $orderTotal = Order::where(['business_uid' => $data['uid'], 'status' => 2, 'name' => '录单'])
                        ->where('updated_at', '>=', $data['time'])
                        ->count();

        //商家信息
        $shopData = BusinessData::where('uid', $data['uid'])->first();
        if (!$shopData)
        {
            throw new LogicException('该商家信息不存在');
        }

        //组装数据
        foreach ($orderList as $key => $val)
        {
            $orderList[$key]['title'] = $val['name'] . ' ' . '让利比例' . intval($val['profit_ratio']) . '%';
            $orderList[$key]['profit_amount'] = sprintf('%.2f', $val['profit_price'] * 0.0175);
        }

        //返回
        return [
            'orderList' => $orderList,
            'orderTotal' => $orderTotal,
            'shopName'   => $shopData->name,
        ];
    }
}
