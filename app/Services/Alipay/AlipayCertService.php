<?php

namespace App\Services\Alipay;

use AlipayAop\AopCertClient;
use AlipayAop\request\AlipayTradeQueryRequest;
use AlipayAop\request\AlipayUserInfoAuthRequest;
use Exception;

class AlipayCertService
{
    protected $config = [
        '' => '',
    ];
    
    public function __construct(array $config = null)
    {
    }
    
    public function getRequest()
    {
    }
    
    public function authByCert()
    {
        try {
            $AopCertClient = new AopCertClient();
            $appCertPath = config('alipay.app_public_cert_path');
            $alipayCertPath = config('alipay.alipay_public_cert_path');
            $rootCertPath = config('alipay.alipay_root_cert_path');
            $AopCertClient->gatewayUrl = config('alipay.gateway');
            $AopCertClient->appId = config('alipay.app_id');
            $AopCertClient->rsaPrivateKey = config('alipay.app_private_key');
            $AopCertClient->signType = config('alipay.sign_type');
            $AopCertClient->alipayrsaPublicKey = $AopCertClient->getPublicKey($alipayCertPath);                       //调用getPublicKey从支付宝公钥证书中提取公钥
            $AopCertClient->appCertSN = $AopCertClient->getCertSN($appCertPath);                                      //调用getCertSN获取证书序列号
            $AopCertClient->alipayRootCertSN = $AopCertClient->getRootCertSN($rootCertPath);                          //调用getRootCertSN获取支付宝根证书序列号
            $AopCertClient->apiVersion = '1.0';
            $AopCertClient->postCharset = 'UTF-8';
            $AopCertClient->format = 'json';
            $request = new AlipayUserInfoAuthRequest();
            $data = [
                'scopes' => ['auth_base'],
                'state'  => 'init',
            ];
            $request->setBizContent(json_encode($data));
            $result = $AopCertClient->pageExecute($request);
        } catch (Exception $e) {
            throw $e;
        }
        return $result;
    }
    
    public function orderByCert()
    {
        try {
            $AopCertClient = new AopCertClient();
            $appCertPath = config('alipay.app_public_cert_path');
            $alipayCertPath = config('alipay.alipay_public_cert_path');
            $rootCertPath = config('alipay.alipay_root_cert_path');
            $AopCertClient->gatewayUrl = config('alipay.gateway');
            $AopCertClient->appId = config('alipay.app_id');
            $AopCertClient->rsaPrivateKey = config('alipay.app_private_key');
            $AopCertClient->alipayrsaPublicKey = $AopCertClient->getPublicKey($alipayCertPath);//调用getPublicKey从支付宝公钥证书中提取公钥
            $AopCertClient->apiVersion = '1.0';
            $AopCertClient->signType = 'RSA2';
            $AopCertClient->postCharset = 'utf-8';
            $AopCertClient->format = 'json';
            $AopCertClient->isCheckAlipayPublicCert = false;                                 //是否校验自动下载的支付宝公钥证书，如果开启校验要保证支付宝根证书在有效期内
            $AopCertClient->appCertSN = $AopCertClient->getCertSN($appCertPath);             //调用getCertSN获取证书序列号
            $AopCertClient->alipayRootCertSN = $AopCertClient->getRootCertSN($rootCertPath); //调用getRootCertSN获取支付宝根证书序列号
            $request = new AlipayTradeQueryRequest();
            $request->setBizContent("{".
                                    "\"out_trade_no\":\"20150320010101001\",".
                                    "\"trade_no\":\"2014112611001004680 073956707\",".
                                    "\"org_pid\":\"2088101117952222\",".
                                    "      \"query_options\":[".
                                    "        \"TRADE_SETTE_INFO\"".
                                    "      ]".
                                    "  }");
            $result = $AopCertClient->execute($request);
//            echo $result;
        } catch (Exception $e) {
            throw $e;
        }
        return $result;
    }
}
