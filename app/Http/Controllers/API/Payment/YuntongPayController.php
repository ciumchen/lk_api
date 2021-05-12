<?php

namespace App\Http\Controllers\api\Payment;

use App\Http\Controllers\API\Order\TradeOrderController;
use App\Http\Controllers\Controller;
use App\Models\TradeOrder;
use Illuminate\Http\Request;
use App\Libs\Yuntong\YuntongPay;

class YuntongPayController extends Controller
{

    public function createTradeOrder($data = [])
    {
        $uid = $data[ 'uid' ];
        $TradeOrder = new TradeOrder();
        $totalFee = $totalFee = $data[ 'money' ] * $data[ 'number' ];
        switch ($data[ 'payChannel' ]) {
            case 'alipay':
                $payChannel = 'alipay';
                break;
            case 'wx':
                $payChannel = 'wx';
                break;
            default:
                $payChannel = '';;
        }
        $date = date("Y-m-d H:i:s");
        $tradeOrderData = [
            'order_no'      => $TradeOrder->CreateOrderNo(),
            'user_id'       => $uid,
            'numeric'       => $data[ 'numeric' ],
            'telecom'       => $data[ 'telecom' ],
            'title'         => $data[ 'goodsTitle' ],
            'price'         => $data[ 'money' ],
            'num'           => $data[ 'number' ],
            'description'   => $data[ 'description' ],
            'pay_time'      => time(),
            'end_time'      => time(),
            'status'        => 'await',
            'remarks'       => '',
            'order_from'    => $payChannel,
            'need_fee'      => sprintf("%.2f", $totalFee),
            'profit_price'  => 0,
            'profit_ratio'  => 0,
            'created_at'    => $date,
            'modified_time' => $date,
        ];
        $TradeOrderController = new TradeOrderController();
        $TradeOrderController->setOrder($tradeOrderData, $uid);
    }

    public function createOrder($data = [])
    {
        $uid = $data[ 'uid' ];
        switch ($data[ 'description' ]) {
            case "MT":
                $name = '美团';
                break;
            case "HF":
                $name = '话费';
                break;
            case "YK":
                $name = '油卡';
                break;
            default:
                $name = '';
        }
        $profit_ratio = $this->getProfitRatio($name);
        $profit_price = $data[ 'money' ] * ($profit_ratio / 100);
        $date = date("Y-m-d H:i:s");
        $orderData = [
            'uid'          => $uid,
            'business_uid' => 2,
            'name'         => $name,
            'profit_ratio' => $profit_ratio,
            'price'        => $data[ 'money' ],
            'profit_price' => sprintf("%.2f", $profit_price),
            'pay_status'   => 'await',
            'created_at'   => $date,
            'updated_at'   => $date,
        ];
    }


    /**
     * 组装订单数据
     * @param $data
     * @param TradeOrder|null $TradeOrder
     * @return array
     */
    public function createData($data, TradeOrder $TradeOrder = null)
    {
        $uid = $data[ 'uid' ];
        if ( !$TradeOrder) {
            $TradeOrder = new TradeOrder();
        }
        switch ($data[ 'description' ]) {
            case "MT":
                $name = '美团';
                break;
            case "HF":
                $name = '话费';
                break;
            case "YK":
                $name = '油卡';
                break;
            default:
                $name = '';
        }
        $profit_ratio = $this->getProfitRatio($name);
        $totalFee = $totalFee = $data[ 'money' ] * $data[ 'number' ];
        $profit_price = $data[ 'money' ] * ($profit_ratio / 100);
        $payChannel = $this->getPayChannel($data[ 'payChannel' ]);
        $date = date("Y-m-d H:i:s");
        $order_no = $TradeOrder->CreateOrderNo();
        $remarks = $this->getRemarks($data[ 'description' ]);
        return [
            'order_no'      => $order_no,
            'user_id'       => $uid,
            'numeric'       => $data[ 'numeric' ],
            'telecom'       => $data[ 'telecom' ],
            'title'         => $data[ 'goodsTitle' ],
            'price'         => $data[ 'money' ],
            'num'           => $data[ 'number' ],
            'description'   => $data[ 'description' ],
            'name'          => $name,
            'pay_time'      => time(),
            'end_time'      => time(),
            'status'        => 'await',
            'remarks'       => $remarks,
            'order_from'    => $payChannel,
            'need_fee'      => sprintf("%.2f", $totalFee),
            'profit_price'  => $profit_price,
            'profit_ratio'  => $profit_ratio,
            'created_at'    => $date,
            'modified_time' => $date,
        ];
    }

    public function createPay(Request $request)
    {
        if (empty($data)) {
            $data = $request->all();
        }
        $orderData = $this->createData($data);
        return $orderData;
        exit;
        $OrderData = $this->createTradeOrder($data);
        $trade_order = $this->createTradeOrder($data);
        $order = $this->createOrder($data);
        $YuntongPay = new YuntongPay();
    }

    /*******************************************************************/
    public function getRemarks($type)
    {
        //        TODO:获取备注
        switch ($type) {
            default:
                $remarks = '';
        }
        return $remarks;
    }

    /**
     * 获取支付通道标记
     * @param string $type 支付类型
     * @return string
     */
    public function getPayChannel(string $type)
    {
        switch ($type) {
            case 'alipay':
                $payChannel = 'alipay';
                break;
            case 'wx':
                $payChannel = 'wx';
                break;
            case 'unionpay':
                $payChannel = 'unionpay';
                break;
            default:
                $payChannel = '';
        }
        return $payChannel;
    }

    /**
     * 获取比例
     * @param string $type
     * @return int
     */
    public function getProfitRatio(string $type)
    {
        switch ($type) {
            case 'HF':
            case 'YK':
                $profit_ratio = 5;
                break;
            default:
                $profit_ratio = 10;
        }
        return $profit_ratio;
    }


}
