<?php

namespace App\Http\Controllers\API\Order;

use App\Http\Controllers\Controller;
use App\Models\TradeOrder;
use Illuminate\Http\Request;
use GuzzleHttp;
use Illuminate\Support\Facades\Log;

/*
 * 话费、油卡自动充值接口
 */

class RechargeController extends Controller
{
    const openId = 'JH015783944f2e22743e4efb9ad3b04f45'; //正式环境

    /**话费自动充值
     * @param array $data
     * @return mixed
     * @throws
     */
    public function setCall(array $data)
    {
        $url = 'http://op.tianjurenhe.com/ofpay/mobile/onlineorder';
        $key = '2420d8fb789d6ceb1244ac827761dfb0'; //正式环境

        //组装请求数据
        $phoneno = $data['numeric'];
        $cardnum = $data['price'];
        $orderid = $data['order_no'];
        $sign = md5(self::openId . $key . $phoneno . $cardnum . $orderid);
        $http = new GuzzleHttp\Client;

        //调用话费url
        $response = $http->get($url, [
            'query' => [
                'key'     => $key,
                'phoneno' => $phoneno,
                'cardnum' => $cardnum,
                'orderid' => $orderid,
                'sign'    => $sign
            ],
        ]);

        //返回数据
        $res = json_decode( $response->getBody(), 1);
        if ($res['error_code'] == 0)
        {
            return json_encode(['code' => 0, 'msg' => '充值成功']);
        } else
        {
            return json_encode(['code' => -1, 'msg' => '充值失败，' . $res['reason']]);
        }
    }

    /**油卡自动充值
     * @param array $data
     * @return mixed
     * @throws
     */
    public function setGas(array $data)
    {
        $url = 'http://op.tianjurenhe.com/ofpay/sinopec/onlineorder';
        $key = '512a6c9492050f4d0f8f951cec9be05c'; //正式环境

        //组装请求数据
        $userInfoData = (new TradeOrder())->getUser($data['order_no']);
        $cardnum = 1;
        $game_userid = $data['game_userid'];
        $orderid = $data['order_no'];
        if ($data['price'] == 1000)
        {
            $proid = 10004;
        }

        $sign = md5(self::openId . $key . $proid . $cardnum . $game_userid . $orderid);

        $http = new GuzzleHttp\Client;
        //调用油卡url
        $response = $http->get($url, [
            'query' => [
                'proid'       => $proid,
                'cardnum'     => 1,
                'orderid'     => $orderid,
                'game_userid' => $game_userid,
                'gasCardTel'  => $userInfoData->phone,
                'chargeType'  => 1,
                'key'         => $key,
                'sign'        => $sign,
            ],
        ]);

        //返回数据
        $res = json_decode( $response->getBody(), 1);
        if ($res['error_code'] == 0)
        {
            return json_encode(['code' => 0, 'msg' => '充值成功']);
        } else
        {
            return json_encode(['code' => -1, 'msg' => '充值失败，' . $res['reason']]);
        }
    }
}
