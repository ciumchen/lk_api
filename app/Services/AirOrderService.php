<?php

namespace App\Services;

use App\Exceptions\LogicException;
use App\Http\Controllers\API\Airticket\OrderPayBillController;
use App\Models\AirTradeLogs;

class AirOrderService
{
    /**机票数据检查
    * @param array $paramsData
    * @throws
    */
    public function checkParams(array $paramsData)
    {
        if (!$paramsData['seatCode'])
        {
            throw new LogicException('舱位编码必须填写');
        }
        if (!$paramsData['passagers'])
        {
            throw new LogicException('乘客信息必须填写');
        }
        if (!$paramsData['itemId'])
        {
            throw new LogicException('机票标准商品编号必须填写');
        }
        if (!$paramsData['contactName'])
        {
            throw new LogicException('订票联系人必须填写');
        }
        if (!$paramsData['contactTel'])
        {
            throw new LogicException('联系电话必须填写');
        }
        if (!$paramsData['date'])
        {
            throw new LogicException('出发日期必须填写');
        }
        if (!$paramsData['from'])
        {
            throw new LogicException('起飞站点(机场)三字码必须填写');
        }
        if (!$paramsData['to'])
        {
            throw new LogicException('抵达站点(机场)三字码必须填写');
        }
        if (!$paramsData['companyCode'])
        {
            throw new LogicException('航空公司编码必须填写');
        }
        if (!$paramsData['flightNo'])
        {
            throw new LogicException('航班号必须填写');
        }
    }

    /**订单数据
    * @param array $ticketTrade
    * @return array
    * @throws
    */
    public function ticketOrder(array $ticketTrade)
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

    /**生成机票订单
     * @param string $orderNo
     * @throws
     */
    public function airOrder(string $orderNo)
    {
        //获取机票信息
        $airTradeLogs = (new AirTradeLogs())->getAirTradeInfo($orderNo);

        //组装机票订单数据
        $airTradeData = [
            'seatCode' => $airTradeLogs->seatCode,
            'passagers' => $airTradeLogs->passagers,
            'itemId' => $airTradeLogs->itemId,
            'contactName' => $airTradeLogs->contactName,
            'contactTel' => $airTradeLogs->contactTel,
            'date' => $airTradeLogs->date,
            'from' => $airTradeLogs->from,
            'to' => $airTradeLogs->to,
            'companyCode' => $airTradeLogs->companyCode,
            'flightNo' => $airTradeLogs->flightNo,
            'aid' => $airTradeLogs->id
        ];

        //生成机票订单
        (new OrderPayBillController())->setOrderPay($airTradeData);
    }
}
