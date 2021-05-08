<?php


namespace App\Libs\Yuntong;


class YuntongPay extends Config
{
    /**
     * @var string
     */
    private string $pay_api = '/v2/pay';

    /**
     * [必须]
     * @var string 订单标题
     */
    private string $goods_title;

    /**
     * [必须]
     * @var string 订单明细
     */
    private string $goods_desc;

    /**
     * [必须]
     * @var string 商户订单号
     */
    private string $order_id;

    /**
     * [必须]
     * @var float 支付金额
     */
    private float $amount;

    /**
     * [必须]
     * @var string 支付类型 固定值：alipay|wx|unionpay
     */
    private string $type;

    /**
     * [必须]
     * @var string 支付方式 固定值：app|wap|qr|mini|pub
     */
    private string $method;

    /**
     * [必须]
     * @var string 异步通知地址
     */
    private string $notify_url;


    /**
     * @var string 同步返回地址 仅支持app和wap渠道
     */
    private string $return_url = '';

    /**
     * @var string 场景 固定值[ios|android]
     */
    private string $scene = '';

    /**
     * @var string 商户id 特定场景传递，默认不传
     */
    private string $merchant_id = '';

    /**
     * 用户端IP
     * 在type为wx，method为wap时必填（微信H5）
     */
    private string $ip = '';

    /**
     * @return bool|string
     * @throws \Exception
     */
    public function pay()
    {
        try {
            $data = [];
            $data['app_id'] = $this->appID;
            $data['goods_title'] = $this->goods_title;
            $data['goods_desc'] = $this->goods_desc;
            $data['order_id'] = $this->order_id;
            $data['amount'] = $this->amount;
            $data['type'] = $this->type;
            $data['method'] = $this->method;
            $data['notify_url'] = $this->notify_url;
            if ($this->return_url) {
                $data['return_url'] = $this->return_url;
            }
            if ($this->scene) {
                $data['scene'] = $this->scene;
            }
            if ($this->merchant_id) {
                $data['merchant_id'] = $this->merchant_id;
            }
            if ($this->ip) {
                $data['ip'] = $this->ip;
            }
            $data['sign'] = Sign::make($data, ['secret' => self::APP_SECRET]);
            dump($data);
//            dd('挂了~！~');
            return Request::PostRequest(self::API_DOMIAN . $this->pay_api, $data);
        } catch (\Exception $e) {
            throw $e;
        }
        return false;


    }

    public function setGoodsTitle($val)
    {
        $this->goods_title = $val;
        return $this;
    }

    public function setGoodsDesc($val)
    {
        $this->goods_desc = $val;
        return $this;

    }

    public function setOrderId($val)
    {
        $this->order_id = $val;
        return $this;
    }

    public function setAmount($val)
    {
        $this->amount = $val;
        return $this;
    }

    public function setType($val)
    {
        $this->type = $val;
        return $this;
    }

    public function setMethod($val)
    {
        $this->method = $val;
        return $this;
    }

    public function setNotifyUrl($val)
    {
        $this->notify_url = $val;
        return $this;
    }

    public function setReturnUrl($val)
    {
        $this->return_url = $val;
        return $this;
    }

    public function setScene($val)
    {
        $this->scene = $val;
        return $this;
    }

    public function setMerchantId($val)
    {
        $this->merchant_id = $val;
        return $this;
    }

    public function setClientIp($val)
    {
        $this->ip = $val;
        return $this;
    }


}
