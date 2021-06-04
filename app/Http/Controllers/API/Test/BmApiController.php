<?php

namespace App\Http\Controllers\api\Test;

use App\Http\Controllers\Controller;
use Bmapi\Api\MobileRecharge\GetItemInfo;
use Bmapi\Api\MobileRecharge\PayBill;
use Bmapi\Api\UtilityBill\GetAccountInfo;
use Bmapi\Api\UtilityBill\ItemList;
use Bmapi\Api\UtilityBill\ItemPropsList;
use Bmapi\Api\UtilityBill\LifeRecharge;
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
    
    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
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
    
    public function utilityRecharge(Request $request)
    {
        $money = $request->input('money');
        $itemId = $request->input('item_id');
        $account = $request->input('account');
        $LIfeRecharge = new LifeRecharge();
        $LIfeRecharge->setItemId($itemId)
                     ->setItemNum($money)
                     ->setRechargeAccount($account)
                     ->getResult();
        $data = $LIfeRecharge->getData();
        return response()->json($data);
    }
    
    /**
     * 手机查话费
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function mobileGetInfo(Request $request)
    {
        $mobile = $request->input('mobile');
        $money = $request->input('money');
        $GetInfo = new GetItemInfo();
        $res = $GetInfo->setMobileNo($mobile)
                       ->setRechargeAmount($money)
                       ->getResult();
        $info = $GetInfo->getItemInfo();
        return response()->json($info);
        /*
        {
            "itemId": "141606",
            "inPrice": "9.960",
            "numberChoice": "1-10",
            "province": "湖北",
            "city": "湖北全省",
            "operator": "移动",
            "itemName": "湖北移动话费10元直充",
            "rechargeAmount": "10",
            "advicePrice": "10.00",
            "mobileNo": "18707145152",
            "reverseFlag": "0"
        }
         */
    }
    
    public function mobilePayBill(Request $request)
    {
        $mobile = $request->input('mobile');
        $money = $request->input('money');
        $PayBill = new PayBill();
        $res = $PayBill->setMobileNo($mobile)
                       ->setRechargeAmount($money)
                       ->getResult();
        $bill = $PayBill->getBill();
        return response()->json($bill);
        /*
        {
            "orderCost": "9.960",
            "orderProfit": "0.040",
            "saleAmount": "10.000",
            "cardPwdList": null,
            "orderTime": "2021-06-03 18:58:33",
            "operateTime": null,
            "payState": "1",
            "rechargeState": "0",
            "supQq": null,
            "classType": "4",
            "itemCost": "9.960",
            "facePrice": "10",
            "supId": "S115281",
            "supNickName": null,
            "supContactUser": null,
            "supMobile": null,
            "billId": "S2106031191902",
            "itemId": "141606",
            "itemNum": "1",
            "rechargeAccount": "18707145152",
            "gameArea": null,
            "gameServer": null,
            "receiveMobile": null,
            "actPrice": null,
            "extPay": null,
            "itemName": "湖北移动话费10元直充",
            "isBatch": null,
            "outerTid": null,
            "userCode": "A5626842",
            "battleAccount": null,
            "openBank": null,
            "cardNo": null,
            "customerName": null,
            "customerTel": null,
            "purchaser": null,
            "buyerTel": null,
            "buyerAddress": null,
            "buyerRemark": null,
            "minConsume": null,
            "packageName": null,
            "preStore": null,
            "idNo": null,
            "idAddress": null,
            "idFrontImage": null,
            "idBackImage": null,
            "remark": null,
            "simCardId": null,
            "mobileNo": null,
            "tplId": "00010001"
        }
        */
    }
}
