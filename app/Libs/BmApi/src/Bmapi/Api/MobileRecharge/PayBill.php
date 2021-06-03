<?php

namespace Bmapi\Api\MobileRecharge;

use Bmapi\core\ApiRequest;

/**
 * 话费订单充值
 * Class PayBill
 *
 * @package Bmapi\Api\MobileRecharge
 */
class PayBill extends ApiRequest
{
    
    protected $method = 'bm.elife.recharge.mobile.payBill';
    
    private $paramsKey = [
        'mobileNo',
        'rechargeAmount',
        'outerTid',
        'callback',
        'itemId',
        'payType',
    ];
    
    private $mobileNo;
    
    private $rechargeAmount;
    
    private $outerTid;
    
    private $callback;
    
    private $itemId;
    
    private $payType;
    
    private $bill;
    
    public function apiParams()
    : array
    {
        $params = [];
        foreach ($this->paramsKey as $k) {
            if (isset($this->$k)) {
                $params[ $k ] = $this->$k;
            }
        };
        return array_merge(parent::apiParams(), $params);
    }
    
    public function fetchResult()
    {
        $result = json_decode($this->result, true);
        if (!is_array($result)) {
            return parent::fetchResult();
        }
        if (array_key_exists('errorToken', $result)) {
            throw new Exception($this->result);
        }
        $this->bill = $result[ 'data' ];
        return $this;
    }
    
    public function getBill()
    {
        return $this->bill;
    }
    
    /*******************************************************/
    public function setMobileNo($val)
    {
        $this->mobileNo = $val;
        return $this;
    }
    
    public function setRechargeAmount($val)
    {
        $this->rechargeAmount = $val;
        return $this;
    }
    
    public function setOuterTid($val)
    {
        $this->outerTid = $val;
        return $this;
    }
    
    public function setCallback($val)
    {
        $this->callback = $val;
        return $this;
    }
    
    public function setItemId($val)
    {
        $this->itemId = $val;
        return $this;
    }
    
    public function setPayType($val)
    {
        $this->payType = $val;
        return $this;
    }
    
    /*******************************************************/
    public function getMobileNo()
    {
        return $this->mobileNo;
    }
    
    public function getRechargeAmount()
    {
        return $this->rechargeAmount;
    }
    
    public function getOuterTid()
    {
        return $this->outerTid;
    }
    
    public function getCallback()
    {
        return $this->callback;
    }
    
    public function getItemId()
    {
        return $this->itemId;
    }
    
    public function getPayType()
    {
        return $this->payType;
    }
}