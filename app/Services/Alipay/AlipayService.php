<?php

namespace App\Services\Alipay;

use AlipayAop\AopClient;
use AlipayAop\AopCertClient;
use AlipayAop\request\AlipayTradeQueryRequest;
use AlipayAop\request\AlipayUserInfoAuthRequest;

class AlipayService extends AlipayBaseService
{
    public function test()
    {
        try {
            return $this->authByKeyWebPage();
        } catch (\Exception $e) {
            throw $e;
        }
    }
    
    public function authByKeyWebPage()
    {
        try {
            $AopClient = $this->getAopClient();
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
