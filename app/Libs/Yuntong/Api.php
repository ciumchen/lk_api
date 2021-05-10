<?php

namespace App\Libs\Yuntong;

trait Api
{

    /**
     * @var string 订单支付接口
     */
    private string $pay_api = '/v2/pay';

    /**
     * @var string 订单查询接口
     */
    private string $query_api = '/v2/query';

    /**
     * @var string 订单退款接口
     */
    private string $refund_api = '/v2/refund';


}
