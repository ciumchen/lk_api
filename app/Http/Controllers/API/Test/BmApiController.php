<?php

namespace App\Http\Controllers\api\Test;

use App\Http\Controllers\Controller;
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
    public function index()
    {
        $ItemList = new ItemList();
        $ItemList->setPageNo(0)
                 ->setPageSize(8)
                 ->setCity('深圳')
                 ->postParams()
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
    public function goodsAttrList()
    {
        $ItemPropsList = new ItemPropsList();
        try {
            $ItemPropsList->setItemId(6420401)
                          ->getResult();
        } catch (\Exception $e) {
            throw $e;
        }
        $res = $ItemPropsList->getList();
        return response()->json($res);
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
}
