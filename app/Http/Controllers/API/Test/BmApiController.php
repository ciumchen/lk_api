<?php

namespace App\Http\Controllers\api\Test;

use App\Http\Controllers\Controller;
use Bmapi\Api\UtilityBill\GetAccountInfo;
use Bmapi\Api\UtilityBill\ItemList;
use Bmapi\Api\UtilityBill\ItemPropsList;
use Bmapi\Conf\Config;
use Bmapi\Core\Aes;
use Bmapi\Core\Sign;
use Bmapi\Core\ApiRequest;
use Illuminate\Http\Request;

/**
 * 斑马力方接口测试
 * Class BmApiController
 *
 * @package App\Http\Controllers\api\Test
 */
class BmApiController extends Controller
{
    
    /**
     * 水电煤商品列表查询测试
     *
     * @throws \Exception
     */
    public function index(Request $request)
    {
        $city = trim($request->input('city'), '市');
        $project_id = $request->input('project_id');
        $ItemList = new ItemList();
        $ItemList->setPageNo(0)
                 ->setPageSize(10)
                 ->setCity($city)
                 ->setProjectId($project_id)
                 ->getResult();
        $res = $ItemList->getList();
        return response()->json($res);
        /*
        [
            {
                "itemId": "6434001",
                "itemName": "广东深圳 深圳供电局 电费户号 直充任意充",
                "inPrice": 1
            },
            {
                "itemId": "6420401",
                "itemName": "广东深圳_深圳市燃气集团股份有限公司_燃气费_户号_直充任意充",
                "inPrice": 1
            },
            {
                "itemId": "64642401",
                "itemName": "广东深圳_深圳水务集团_水费户号_任意充直充",
                "inPrice": 1
            }
        ]
        */
    }
    
    /**
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function goodsAttrList(Request $request)
    {
        $item_id = $request->input('item_id');
        $ItemPropsList = new ItemPropsList();
        try {
            $ItemPropsList->setItemId($item_id)
                          ->getResult();
        } catch (\Exception $e) {
            throw $e;
        }
        $res = $ItemPropsList->getList();
        $nex_params = $ItemPropsList->getNextParams();
        return response()->json($nex_params);
        /*
        [
            {
                "vid": "v0",
                "typeDesc": "充值方式",
                "vname": "直充",
                "type": "CHARGETYPE",
                "vkey": "1"
            },
            {
                "vid": "v2228",
                "typeDesc": "省",
                "vname": "广东",
                "type": "PRVCIN",
                "vkey": "广东"
            },
            {
                "vid": "v2620",
                "typeDesc": "缴费方式",
                "vname": "户号",
                "type": "SPECIAL",
                "vkey": "户号"
            },
            {
                "vid": "v2235",
                "typeDesc": "市",
                "vname": "深圳",
                "type": "CITYIN",
                "vkey": "深圳"
            },
            {
                "vid": "v88008",
                "typeDesc": "缴费单位",
                "vname": "深圳市燃气集团股份有限公司",
                "type": "BRAND",
                "vkey": "深圳市燃气集团股份有限公司"
            },
            {
                "vid": "v2574",
                "typeDesc": "充值模板:",
                "vname": "公共事业类 水电煤直充模板",
                "type": "TPLID",
                "vkey": "00040003"
            },
            {
                "vid": "v2478",
                "typeDesc": "充值类型:",
                "vname": "公用事业充值缴费",
                "type": "CARDTYPE",
                "vkey": "64"
            },
            {
                "vid": "v21",
                "typeDesc": "面值:",
                "vname": "任意充",
                "type": "FACEVALUE",
                "vkey": "1"
            }
        ]
        */
    }
    
    public function getInfo(Request $request)
    {
        $item_id = $request->input('item_id');
        $account = $request->input('account');
        $city = $request->input('city');
        $city_id = $request->input('city_id');
        $mode_id = $request->input('mode_id');
        $mode_type = $request->input('mode_type');
        $project_id = $request->input('project_id');
        $province = $request->input('province');
        $unit_id = $request->input('unit_id');
        $unit_name = $request->input('unit_name');
        $GetAccountInfo = new GetAccountInfo();
        $GetAccountInfo->setItemId($item_id)               // 标准商品编号(页面选择)
                       ->setAccount($account)              // 缴费单标识号（户号或条形码）
                       ->setCity($city)                    // 市名称(后面不带"市"，属性查询接口中返回的参数itemProps-"type": "CITYIN"下的vname)
                       ->setCityId($city_id)               // 城市V编号(属性查询接口中返回的参数itemProps-"type": "CITYIN"下的vid)
                       ->setModeId($mode_id)               // 缴费方式V编号 (属性查询接口中返回的参数itemProps-"type": "SPECIAL"下的vid)
                       ->setModeType($mode_type)           // 缴费方式：1是条形码 2是户号
                       ->setProjectId($project_id)         // 缴费项目编号，水费c2670，电费c2680，气费c2681，(属性查询接口中返回的参数cid)
                       ->setProvince($province)            // 省名称(后面不带"省"，属性查询接口中返回的参数itemProps-"type": "PRVCIN"下的vname)
                       ->setUnitId($unit_id)               // 缴费单位V编号(属性查询接口中返回的参数itemProps-"type": "BRAND"下的vid)
                       ->setUnitName($unit_name)           // 缴费单位名称(属性查询接口中返回的参数itemProps-"type": "BRAND"下的vname)
                       ->getResult();
        $res = $GetAccountInfo->getBill();
        $status = $GetAccountInfo->getStatus();
        $msg = $GetAccountInfo->getMessage();
        return response()->json(['res' => $res, 'status' => $status, 'msg' => $msg]);
    }
}
