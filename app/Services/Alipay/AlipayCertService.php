<?php

namespace App\Services\Alipay;

use AlipayAop\AopCertClient;
use AlipayAop\request\AlipayTradeQueryRequest;
use AlipayAop\request\AlipayUserInfoAuthRequest;
use App\Models\UserAlipayAuthToken;
use Exception;

class AlipayCertService extends AlipayBaseService
{
    public function getRequest()
    {
    }
    
    public function saveUserAuthCode()
    {
    }
    
    public function userBinding($uid)
    {
        $auth_code = UserAlipayAuthToken::getUserAuthCode($uid);
    }
    
    /************************** example **********************************/
    public function authByCertWebPage()
    {
        try {
            $AopCertClient = $this->getAopCertClient();
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
            $AopCertClient = $this->getAopCertClient();
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
        } catch (Exception $e) {
            throw $e;
        }
        return $result;
    }
}
