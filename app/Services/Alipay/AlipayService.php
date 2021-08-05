<?php

namespace App\Services\Alipay;

use AlipayAop\AopClient;
use AlipayAop\AopCertClient;
use AlipayAop\request\AlipayTradeQueryRequest;
use AlipayAop\request\AlipayUserInfoAuthRequest;

class AlipayService
{
    public function test()
    {
        try {
            return $this->authByKey();
        } catch (\Exception $e) {
            throw $e;
        }
    }
    
    public function authByKey()
    {
        try {
            $AopClient = new AopClient();
            $AopClient->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
            $AopClient->appId = config('alipay.app_id');
            $AopClient->rsaPrivateKey = config('alipay.app_private_key');
            $AopClient->alipayrsaPublicKey = config('alipay.rsa_public_key');
            $AopClient->apiVersion = '1.0';
            $AopClient->signType = config('alipay.sign_type');
            $AopClient->postCharset = 'UTF-8';
            $AopClient->return_url = 'UTF-8';
            $AopClient->format = 'json';
            $request = new AlipayUserInfoAuthRequest();
            $data = [
                'scopes' => ['auth_base'],
                'state'  => 'init',
            ];
            $request->setBizContent(json_encode($data));
            $result = $AopClient->pageExecute($request);
        } catch (\Exception $e) {
            throw $e;
        }
        return $result;
    }
}
