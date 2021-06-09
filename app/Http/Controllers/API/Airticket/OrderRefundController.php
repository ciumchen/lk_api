<?php

namespace App\Http\Controllers\API\Airticket;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Bmapi\Api\Air\OrderRefund;
use App\Exceptions\LogicException;
use App\Models\OrderAirTrade;

/** 退订飞机票订单 **/
class OrderRefundController extends Controller
{
    /**退订单
    * @param Request $request
    * @return mixed
    * @throws
    */
    public function airRefund(Request $request)
    {
        //获取数据
        $orderRefund = new OrderRefund();
        $refundInfo = $orderRefund
            ->setTradeNo($request->tradeNo)
            ->setReturnType($request->returnType)
            ->setOrderNos($request->orderNos)
            ->postParams()
            ->getResult();

        $refundArr = json_decode($refundInfo, 1);

        if ($refundArr['code'] && $refundArr['code'] == 9)
        {
            throw new LogicException('确认交易子单状态为出票成功，请勿重复操作');
        }

        //组装机票订单数据
        $airTradeData = explode(',', $request->orderNos);
        $ticketTrade = $refundArr['air_order_refund_response'];
        if ($ticketTrade['result'])
        {
            (new OrderAirTrade())->updAirTrade($request->tradeNo, $airTradeData);
            return json_encode(['code' => 1, 'msg' => '机票退订成功']);
        }
    }
}
