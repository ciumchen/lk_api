<?php

namespace App\Http\Controllers\API\Payment;

use App\Exceptions\LogicException;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Setting;
use App\Models\TradeOrder;
use App\Models\Traits\AllowField;
use App\Services\bmapi\VideoCardService;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Libs\Yuntong\YuntongPay;
use App\Models\AirTradeLogs;
use Exception;
use Illuminate\Support\Facades\DB;
use Log;

class YuntongPayController extends Controller
{
    use AllowField;

    /**
     * 创建支付
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws Exception
     * @throws \Throwable
     */
    public function createPay(Request $request)
    {
        $data = $request->all();
        $uid = $data[ 'uid' ] ?: 0;
        $tradeOrder = new TradeOrder();
        $priceList = [];
        if (in_array($data[ 'description' ], ['HF', 'ZL', 'MT', 'YK'])) {
            $type = $this->sysPriceType($data[ 'description' ]);
            $priceList = (new Setting())->getSysPrice($type);
        }
        //判断支付金额
        if (!in_array($data[ 'money' ], $priceList) && in_array($data[ 'description' ], ['HF', 'ZL'])) {
            throw new LogicException('话费充值金额不在可选值范围内');
        } else {
            if (!in_array($data[ 'money' ], $priceList) && $data[ 'description' ] == "MT") {
                throw new LogicException('美团充值金额不在可选值范围内');
            } else {
                if (!in_array($data[ 'money' ], $priceList) && $data[ 'description' ] == "YK") {
                    throw new LogicException('油卡充值金额不在可选值范围内');
                }
            }
        }
        //检查用户当月消费金额
        $sumData = [
            'uid'         => $uid,
            'description' => $data[ 'description' ],
        ];
        $totalPrice = $tradeOrder->getMonthSum($sumData);
        if ($data[ 'description' ] == 'HF' && ($data[ 'money' ] + $totalPrice) >= 500) {
            throw new LogicException('本月话费充值金额已达上限');
        } else {
            if ($data[ 'description' ] == 'YK' && ($data[ 'money' ] + $totalPrice) >= 2000) {
                throw new LogicException('本月油卡充值金额已达上限');
            }
        }
        $orderData = $this->createData($data);
        //油卡需要多传两个参数
        if (in_array($data['description'], ['YK']))
        {
            $orderData['idcard'] = $data['idcard'];
            $orderData['user_name'] = $data['userName'];
        }

        DB::beginTransaction();
        try {
            $oid = $this->createOrder($orderData);
            if (!is_numeric($oid)) {
                throw new Exception('订单生成失败');
            }
            $orderData[ 'oid' ] = $oid;
            $this->createTradeOrder($orderData);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
        DB::commit();
        return $this->payRequest(array_merge($data, $orderData));
    }

    /**
     * 再次请求支付
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws LogicException
     * @throws Exception
     */
    public function againPay(Request $request)
    {
        $data = $request->all();
        if (array_key_exists('deviceInfo', $data)) {
            $data[ 'ip' ] = $data[ 'deviceInfo' ][ 'device_ip' ];
        }
        $TradeOrder = new TradeOrder();
        $order_data = $TradeOrder->where('oid', '=', $data[ 'oid' ])
                                 ->first();
        if (in_array($order_data->status, ['pending', 'succeeded'])) {
            throw new LogicException('订单不属于未支付或支付失败状态');
        }
        //油卡需要多传两个参数
        if (in_array($data['description'], ['YK']))
        {
            $orderData['idcard'] = $data['idcard'];
            $orderData['user_name'] = $data['userName'];
        }

        try {
            if (empty($order_data->end_time)) {
                $order_data->end_time = date('Y-m-d H:i:s');
                $order_data->order_from = $this->getPayChannel($data[ 'payChannel' ]);
                $order_data->save();
                $orderData = $order_data->toArray();
            } else {
                $order_data = $order_data->toArray();
                $orderData = $this->createData(array_merge($data, $order_data));
                $orderData[ 'order_from' ] = $this->getPayChannel($data[ 'payChannel' ]);
                $this->createTradeOrder($orderData);
            }
            return $this->payRequest(array_merge($data, $orderData));
        } catch (Exception $e) {
            throw new LogicException($e->getMessage());
        }
    }

    /**
     * 发起支付请求
     *
     * @param array  $data    支付请求数据[
     *                        'goodsTitle'=>'',
     *                        'goodsDesc'=>'',
     *                        'need_fee'=>'',
     *                        'order_no'=>'',
     *                        'order_from'=>'',
     *                        'ip'=>'',
     *                        'return_url'=>'',
     *                        ]
     *
     * @param string $return_url
     *
     * @return JsonResponse
     * @throws \Exception
     */
    public function payRequest($data, $return_url = '')
    {
        if (empty($return_url)) {
            $return_url = createNotifyUrl('/api/yun-notify');
        }
        $YuntongPay = new YuntongPay();
        try {
            $res = $YuntongPay
                ->setGoodsTitle($data[ 'goodsTitle' ] ?? $data[ 'title' ])
                ->setGoodsDesc($data[ 'goodsDesc' ] ?? '商品描述')
                ->setAmount($data[ 'need_fee' ])
                ->setOrderId($data[ 'order_no' ])
                ->setNotifyUrl($return_url)
                ->setType($data[ 'order_from' ])
                ->setMethod('wap');
            if (isset($data[ 'ip' ])) {
                $res = $res->setIp($data[ 'ip' ]);
            }
            if (isset($data[ 'return_url' ])) {
                $res = $res->setReturnUrl($data[ 'return_url' ]);
            }
            $res = $res->pay();
            $response = json_decode($res, true);
        } catch (Exception $e) {
            throw $e;
        }
        return response()->json(['url' => $response[ 'pay_url' ]]);
    }

    /*******************************************************************/
    /**
     * 订单 trade_order 表插入数据
     *
     * @param array $data 已经组装好的订单数据
     *
     * @return bool
     * @throws Exception
     */
    public function createTradeOrder($data = [])
    {
        try {
            if (array_key_exists('profit_ratio', $data)) {
                $data[ 'profit_ratio' ] = ($data[ 'profit_ratio' ] / 100);
            }
            $TradeOrder = new TradeOrder();
            $data = $this->allowFiled($data, $TradeOrder);
            return $TradeOrder->setOrder($data);
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * 订单 order 表插入数据
     *
     * @param array $data
     *
     * @return int
     * @throws Exception
     */
    public function createOrder($data = [])
    {
        try {
            $Order = new Order();
            $oid = intval($data[ 'oid' ]);
            if ($oid > 0) {
                $order_info = $Order->find($oid);
                if ($order_info) {
                    return $oid;
                }
            }
            $data = $this->allowFiled($data, $Order);
            if (isset($data[ 'status' ])) {
                unset($data[ 'status' ]);
            }
            return $Order->setOrder($data);
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * 组装订单数据
     *
     * @param                   $data
     * @param TradeOrder|null   $TradeOrder
     *
     * @return array
     */
    public function createData($data, TradeOrder $TradeOrder = null)
    {
        $uid = $data[ 'uid' ] ?? $data[ 'user_id' ];
        if (!$TradeOrder) {
            $TradeOrder = new TradeOrder();
        }
        $name = $this->getName($data[ 'description' ] ?? '');
        $remarks = $this->getRemarks($data[ 'description' ] ?? '', $data);
        $profit_ratio = $this->getProfitRatio($data[ 'description' ]);
        $totalFee = $data[ 'need_fee' ] ?? ($data[ 'money' ] * $data[ 'number' ]);
        $profit_price = $data[ 'need_fee' ] ?? ($data[ 'money' ] * ($profit_ratio / 100));
        $payChannel = $this->getPayChannel($data[ 'payChannel' ]);
        $ip = $this->getClientIP($data[ 'payChannel' ], $data);
        $return_url = $this->getReturnUrl($data[ 'returnUrl' ] ?? '');
        $date = date("Y-m-d H:i:s");
        $order_no = $TradeOrder->CreateOrderNo();
        $oid = $this->getOrderId($data[ 'description' ], $data);
        return [
            'order_no'      => $order_no,
            'user_id'       => $uid,
            'uid'           => $uid,
            'business_uid'  => 2,
            'numeric'       => $data[ 'numeric' ],
            'telecom'       => $data[ 'telecom' ],
            'title'         => $data[ 'title' ] ?? $data[ 'goodsTitle' ],
            'price'         => $data[ 'price' ] ?? $data[ 'money' ],
            'num'           => $data[ 'num' ] ?? $data[ 'number' ],
            'description'   => $data[ 'description' ],
            'name'          => $name,
            'oid'           => $oid,
            'integral'      => 0.00,
            'pay_time'      => $date,
            'end_time'      => $date,
            'status'        => 'await',
            'pay_status'    => 'await',
            'ip'            => $ip,
            'return_url'    => $return_url,
            'remarks'       => $remarks,
            'order_from'    => $payChannel,
            'need_fee'      => sprintf("%.2f", $totalFee),
            'profit_price'  => $profit_price,
            'profit_ratio'  => $profit_ratio,
            'created_at'    => $date,
            'modified_time' => $date,
        ];
    }

    /**
     * @param string $url
     *
     * @return string
     */
    public function getReturnUrl(string $url)
    {
        switch (true) {
            case (strpos($url, 'http') !== false):
            case (strpos($url, 'https') !== false):
            case (empty($url)):
                break;
            default:
                $url = url('').'/'.$url;
        }
        return $url;
    }

    /**
     * 获取 order 表中 name 字段的值
     *
     * @param $type
     *
     * @return string
     */
    public function getName($type)
    {
        switch ($type) {
            case "MT":
                $name = '美团';
                break;
            case "HF":
                $name = '话费';
                break;
            case "YK":
                $name = '油卡';
                break;
            case "DD":
                $name = '滴滴';
                break;
            case "ZL":
                $name = '代充';
                break;
            case "AT":
                $name = '飞机票';
                break;
            default:
                $name = '';
        }
        return $name;
    }

    /**
     * 获取 trade_order 表 oid 字段
     *
     * @param         $type
     * @param array   $data
     *
     * @return mixed|string
     */
    public function getOrderId($type, $data = [])
    {
        switch ($type) {
            case 'LR':
                $oid = $data[ 'orderId' ] ?? $data[ 'oid' ];
                break;
            default:
                $oid = 0;
        }
        return $oid;
    }

    /**
     * 获取客户端IP
     *
     * @param $channel
     * @param $data
     *
     * @return mixed|string
     */
    public function getClientIP($channel, $data)
    {
        $ip = '';
        if ($channel == 'wx' || isset($data[ 'deviceInfo' ])) {
            if (is_array($data[ 'deviceInfo' ])) {
                $deviceInfo = $data[ 'deviceInfo' ];
            } else {
                $deviceInfo = json_decode($data[ 'deviceInfo' ], true);
            }
            $ip = $deviceInfo[ 'device_ip' ];
        }
        return $ip;
    }

    /**
     * 获取 trade_order 表中的 remarks 字段值
     *
     * @param         $type
     * @param array   $data
     *
     * @return string
     */
    public function getRemarks($type, $data = [])
    {
        switch ($type) {
            case 'MT':
                $remarks = $data[ 'name' ] ?? '美团用户';
                break;
            default:
                $remarks = '';
        }
        return $remarks;
    }

    /**
     * 获取支付通道标记
     *
     * @param string $type 支付类型
     *
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
     *
     * @param string $type
     *
     * @return int
     */
    public function getProfitRatio(string $type)
    {
        $typeArr = [
            'HF' => 'hf',
            'YK' => 'yk',
            'MT' => 'mt',
            'DD' => 'dd',
            'ZL' => 'zl',
            'AT' => 'at',
        ];
        if ($type != 'LR' && isset($typeArr[ $type ])) {
            $typeStr = "set_business_rebate_scale_".$typeArr[ $type ];
            $profit_ratio = Setting::where('key', $typeStr)->value('value');
        } else {
            $profit_ratio = 10;
        }
        return $profit_ratio;
    }

    /**机票支付
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws Exception
     */
    public function airPay(Request $request)
    {
        $data = $request->all();
        return $this->airCreatePay($data, 1);
    }

    /**机票发起支付请求
     *
     * @param array  $data
     * @param string $type
     *
     * @return JsonResponse
     * @throws Exception
     */
    public function airCreatePay(array $data, string $type)
    {
        //$return_url = 'http://lkapi.com/api/air-notify';
        $return_url = url('/api/air-notify');
        $data[ 'need_fee' ] = $data[ 'money' ] * $data[ 'number' ];
        $data[ 'order_from' ] = $data[ 'payChannel' ];
        $date = date('Y-m-d H:i:s');
        $data[ 'order_no' ] = $type == 1 ? 'PY_'.date('YmdHis').rand(100000, 999999) : $data[ 'order_no' ];
        $orderData = [
            'uid'          => $data[ 'uid' ],
            'business_uid' => 2,
            'profit_ratio' => 5,
            'price'        => $data[ 'money' ] * $data[ 'number' ],
            'profit_price' => $data[ 'money' ] * 0.05,
            'status'       => 1,
            'name'         => $this->getName($data[ 'description' ] ?? ''),
            'remark'       => '',
            'state'        => 1,
            'pay_status'   => 'await',
            'order_no'     => $data[ 'order_no' ],
            'created_at'   => $date,
            'updated_at'   => $date,
        ];
        $airData = [
            'seatCode'    => $data[ 'seatCode' ],
            'passagers'   => $data[ 'passagers' ],
            'itemId'      => $data[ 'itemId' ],
            'contactName' => $data[ 'contactName' ],
            'contactTel'  => $data[ 'contactTel' ],
            'date'        => $data[ 'date' ],
            'from'        => $data[ 'from' ],
            'to'          => $data[ 'to' ],
            'companyCode' => $data[ 'companyCode' ],
            'flightNo'    => $data[ 'flightNo' ],
            'orderNo'     => $data[ 'order_no' ],
            'payChannel'  => $data[ 'payChannel' ],
            'price'       => $data[ 'money' ] * $data[ 'number' ],
        ];
        //只有新增订单才生成新记录
        if ($type == 1) {
            (new Order())->setOrder($orderData);
            (new AirTradeLogs())->setAitTrade($airData);
        }
        return $this->payRequest($data, $return_url);
    }

    /**
     * 机票再次请求支付
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws LogicException
     * @throws Exception
     */
    public function airAgainPay(Request $request)
    {
        //获取数据
        $data = $request->all();
        $airOrder = new Order();
        $airOrderData = json_decode($airOrder->getOrderInfo($data[ 'order_no' ]), 1);
        if (in_array($airOrderData[ 'pay_status' ], ['pending', 'succeeded'])) {
            throw new LogicException('订单不属于未支付或支付失败状态');
        }
        $airOrderData = (array) $airOrderData;
        //组装数据
        $paramsData = [
            'description' => 'AT',
            'payChannel'  => $data[ 'payChannel' ],
            'number'      => 1,
            'numeric'     => '',
            'telecom'     => '',
            'goodsTitle'  => '机票订单',
            'need_fee'    => $airOrderData[ 'price' ],
        ];
        $orderData = array_merge($data, $paramsData);
        $orderData[ 'order_from' ] = $this->getPayChannel($data[ 'payChannel' ]);
        $orderData[ 'money' ] = $airOrderData[ 'price' ];
        $orderData[ 'number' ] = 1;
        $airTradeData = json_decode((new AirTradeLogs())->getAirTradeInfo($data[ 'order_no' ]), 1);
        $airTrades = [
            'seatCode'    => $airTradeData[ 'seat_code' ],
            'passagers'   => $airTradeData[ 'passagers' ],
            'itemId'      => $airTradeData[ 'item_id' ],
            'contactName' => $airTradeData[ 'contact_name' ],
            'contactTel'  => $airTradeData[ 'contact_tel' ],
            'date'        => $airTradeData[ 'date' ],
            'from'        => $airTradeData[ 'from' ],
            'to'          => $airTradeData[ 'to' ],
            'companyCode' => $airTradeData[ 'company_code' ],
            'flightNo'    => $airTradeData[ 'flight_no' ],
            'payChannel'  => $airTradeData[ 'pay_channel' ],
            'price'       => $airTradeData[ 'price' ],
        ];
        try {
            $orderNo = (new Order())->airOrder($data[ 'order_no' ]);
            $airOrderData[ 'order_no' ] = $orderNo;
        } catch (Exception $e) {
            throw new LogicException($e->getMessage());
        }
        //发起支付请求
        return $this->airCreatePay(array_merge($orderData, $airOrderData, $airTrades), 2);
    }

    /**
     * Description:斑马接口订单支付
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\LogicException
     * @throws \Throwable
     * @author lidong<947714443@qq.com>
     * @date   2021/6/11 0011
     */
    public function bmPay(Request $request)
    {
        $order_id = $request->input('oid');
        $goodsDesc = $request->input('goodsDesc');
        $deviceInfo = $request->input('deviceInfo');
        $payChannel = $request->input('payChannel');
        $return_url = $request->input('return_url');
        try {
            $Order = new Order();
            $OrderService = new OrderService();
            $orderInfo = $Order->find($order_id);
            if ($orderInfo->pay_status != 'await') {
                throw new Exception('订单不属于待支付状态');
            }
            $order_no = $orderInfo->order_no;
            $description = $OrderService->getDescription($order_id, $orderInfo);
            if (!empty($orderInfo->updated_at)) {
                /* 更新订单号 */
                $order_no = createOrderNo();
                $OrderService->updateOrderNoSubOrder($order_id, $order_no, $description, $orderInfo);
                $orderInfo->order_no = $order_no;
            }
            $orderInfo->updated_at = time();
            $orderInfo->save();
            if (!is_array($deviceInfo)) {
                $deviceInfo = json_decode($deviceInfo, true);
            }
            $ip = $deviceInfo[ 'device_ip' ] ?? '';
            $data = [
                'goodsTitle' => $orderInfo->name,
                'goodsDesc'  => $goodsDesc,
                'need_fee'   => $orderInfo->price,
                'order_no'   => $order_no,
                'order_from' => $payChannel,
                'ip'         => $ip,
                'return_url' => $return_url,
            ];
            return $this->payRequest($data, createNotifyUrl('api/bm-pay-notify'));
        } catch (Exception $e) {
            throw $e;
            throw new LogicException($e->getMessage());
        }
    }

    /**
     * 设置充值金额参数
     *
     * @param string $description
     *
     * @return string
     */
    public function sysPriceType(string $description)
    {
        switch ($description) {
            case "MT":
                $priceType = 'mt';
                break;
            case "HF":
                $priceType = 'hf';
                break;
            case "YK":
                $priceType = 'yk';
                break;
            case "DD":
                $priceType = 'dd';
                break;
            case "ZL":
                $priceType = 'zl';
                break;
            default:
                $priceType = '';
        }
        return $priceType;
    }
}
