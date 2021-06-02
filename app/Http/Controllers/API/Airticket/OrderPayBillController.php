<?php

namespace App\Http\Controllers\API\Airticket;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Bmapi\Api\Air\OrderPayBill;
use App\Http\Requests\OrderPayRequest;
use App\Models\OrderAirTrade;

/** 生成机票订单 **/
class OrderPayBillController extends Controller
{
    /**新增机票订单数据
    * @param OrderPayRequest $request
    * @throws
    */
    public function orderPay(OrderPayRequest $request)
    {
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
        $ticketTrade = $orderArr['air_order_pay_response']['ticketTrade'];

        //返回数据
        $orderTradeArr = $this->ticketOrder($ticketTrade);

        //写入机票订单数据
        (new OrderAirTrade())->setAirTrade($orderTradeArr);
    }

    /**新增机票订单数据
    * @param array $data
    * @throws
    */
    public function setOrderPay(array $data)
    {
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

        //返回数据
        $orderTradeArr = $this->ticketOrder($ticketTrade);

        //写入机票订单数据
        (new OrderAirTrade())->setAirTrade($orderTradeArr);
    }

    /**订单数据
    * @param array $ticketTrade
    * @return array
    * @throws
    */
    private function ticketOrder(array $ticketTrade)
    {
         //组装订单数据
         $airTradeArr = [];
         $date = date('Y-m-d H:i:s');
         foreach ($ticketTrade['ticketOrders']['ticketOrder'] as $key => $value)
         {
             $airTradeArr[$key]['trade_no']         = $ticketTrade['tradeNo'];
             $airTradeArr[$key]['total_face_price'] = $ticketTrade['totalFacePrice'];
             $airTradeArr[$key]['total_other_fee']  = $ticketTrade['totalOtherFee'];
             $airTradeArr[$key]['total_pay_cash']   = $ticketTrade['totalPayCash'];
             $airTradeArr[$key]['order_type']       = $ticketTrade['orderType'];
             $airTradeArr[$key]['state']            = $ticketTrade['state'];
             $airTradeArr[$key]['legs']             = $ticketTrade['legs'];
             $airTradeArr[$key]['contact_tel']      = $ticketTrade['contactTel'];
             $airTradeArr[$key]['contact_name']     = $ticketTrade['contactName'];
             $airTradeArr[$key]['start_time']       = $ticketTrade['startTime'];
             $airTradeArr[$key]['start_station']    = $ticketTrade['startStation'];
             $airTradeArr[$key]['recevie_station']  = $ticketTrade['recevieStation'];
             $airTradeArr[$key]['train_no']         = $ticketTrade['trainNo'];
             $airTradeArr[$key]['bill_time']        = $ticketTrade['billTime'];
             $airTradeArr[$key]['etime']            = $ticketTrade['etime'];
             $airTradeArr[$key]['bill_state']       = $ticketTrade['billState'];
             $airTradeArr[$key]['title']            = $ticketTrade['title'];
             $airTradeArr[$key]['ctime']            = $ticketTrade['ctime'];
             $airTradeArr[$key]['utime']            = $date;
             $airTradeArr[$key]['remark']           = $ticketTrade['remark'];
             $airTradeArr[$key]['aid']              = $ticketTrade['aid'];
             $airTradeArr[$key]['order_no']         = $value['orderNo'];
             $airTradeArr[$key]['order_state']      = $value['state'];
             $airTradeArr[$key]['item_id']          = $value['itemId'];
             $airTradeArr[$key]['passenger_name']   = $value['passengerName'];
             $airTradeArr[$key]['passenger_tel']    = $value['passengerTel'];
             $airTradeArr[$key]['idcard_type']      = $value['idcardType'];
             $airTradeArr[$key]['idcard_no']        = $value['idcardNo'];
             $airTradeArr[$key]['ticket_no']        = $value['ticketNo'];
             $airTradeArr[$key]['pay_cash']         = $value['payCash'];
             $airTradeArr[$key]['other_fee']        = $value['otherFee'];
             $airTradeArr[$key]['refund_fee']       = $value['refundFee'];
             $airTradeArr[$key]['seat_type']        = $value['seatType'];
        }

        //返回数组
        return $airTradeArr;
    }
}
