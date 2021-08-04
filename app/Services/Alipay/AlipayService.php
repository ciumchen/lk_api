<?php

namespace App\Services\Alipay;

use AlipayAop\AopClient;
use AlipayAop\AopCertClient;
use AlipayAop\AlipayTradeQueryRequest;

class AlipayService
{
    public function test()
    {
        try {
            $aop = new AopCertClient ();
            $appCertPath = "应用证书路径（要确保证书文件可读），例如：/home/admin/cert/appCertPublicKey.crt";
            $alipayCertPath = "支付宝公钥证书路径（要确保证书文件可读），例如：/home/admin/cert/alipayCertPublicKey_RSA2.crt";
            $rootCertPath = "支付宝根证书路径（要确保证书文件可读），例如：/home/admin/cert/alipayRootCert.crt";
    
            $aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
            $aop->appId = '你的appid';
            $aop->rsaPrivateKey = '你的应用私钥';
            $aop->alipayrsaPublicKey = $aop->getPublicKey($alipayCertPath);//调用getPublicKey从支付宝公钥证书中提取公钥
            $aop->apiVersion = '1.0';
            $aop->signType = 'RSA2';
            $aop->postCharset = 'utf-8';
            $aop->format = 'json';
            $aop->isCheckAlipayPublicCert = true;//是否校验自动下载的支付宝公钥证书，如果开启校验要保证支付宝根证书在有效期内
            $aop->appCertSN = $aop->getCertSN($appCertPath);//调用getCertSN获取证书序列号
            $aop->alipayRootCertSN = $aop->getRootCertSN($rootCertPath);//调用getRootCertSN获取支付宝根证书序列号
    
            $request = new AlipayTradeQueryRequest ();
            $request->setBizContent("{" .
                                    "\"out_trade_no\":\"20150320010101001\"," .
                                    "\"trade_no\":\"2014112611001004680 073956707\"," .
                                    "\"org_pid\":\"2088101117952222\"," .
                                    "      \"query_options\":[" .
                                    "        \"TRADE_SETTE_INFO\"" .
                                    "      ]" .
                                    "  }");
            $result = $aop->execute($request);
            echo $result;
        } catch (\Exception $e) {
            dd('1');
            echo $e->getMessage();
        }
        return $aa;
    }
}
