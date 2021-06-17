<?php

namespace App\Http\Controllers\API\Airticket;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Bmapi\Api\Air\OrderPayBill;
use App\Models\OrderAirTrade;
use App\Services\AirOrderService;

/** 生成机票订单 **/
class OrderPayBillController extends Controller
{
    /**新增机票订单数据
    * @param Request $request
    * @throws
    */
    /*public function orderPay(Request $request)
    {
        $data = $request->all();
        (new AirOrderService())->checkParams($data);
        //获取数据
        $orderPay = new OrderPayBill();
        $orderInfo = $orderPay
            ->setSeatCode($request->seatCode)
            ->setPassagers($request->passagers)
            ->setItemId($request->itemId)
            ->setContactName($request->contactName)
            ->setContactTel($request->contactTel)
            ->setDate($request->date)
            ->setFrom($request->from)
            ->setTo($request->to)
            ->setCompanyCode($request->companyCode)
            ->setFlightNo($request->flightNo)
            ->postParams()
            ->getResult();

        $orderArr = json_decode($orderInfo, 1);

        //组装机票订单数据
        //$ticketTrade = $orderArr['air_order_pay_response']['ticketTrade'];
        //$orderTradeArr = (new AirOrderService())->ticketOrder($ticketTrade);

        //写入机票订单数据
        //(new OrderAirTrade())->setAirTrade($orderTradeArr);
    }*/

    /**新增机票订单数据
    * @param array $data
    * @throws
    */
    public function setOrderPay(array $data)
    {
        //验证数据
        (new AirOrderService())->checkParams($data);

        //获取数据
        $orderPay = new OrderPayBill();
        $orderInfo = $orderPay
            ->setSeatCode($data['seatCode'])
            ->setPassagers($data['passagers'])
            ->setItemId($data['itemId'])
            ->setContactName($data['contactName'])
            ->setContactTel($data['contactTel'])
            ->setDate($data['date'])
            ->setFrom($data['from'])
            ->setTo($data['to'])
            ->setCompanyCode($data['companyCode'])
            ->setFlightNo($data['flightNo'])
            ->postParams()
            ->getResult();

        $orderArr = json_decode($orderInfo, 1);

        //组装机票订单数据
        $ticketTrade = $orderArr['air_order_pay_response']['ticketTrade'];
        $ticketTrade['aid'] = $data['aid'];
        $ticketTrade['oid'] = $data['oid'];
        $ticketTrade['etime'] = date('Y-m-d H:i:s');
        $orderTradeArr = (new AirOrderService())->ticketOrder($ticketTrade);

        //写入机票订单数据
        (new OrderAirTrade())->setAirTrade($orderTradeArr);
    }
}
