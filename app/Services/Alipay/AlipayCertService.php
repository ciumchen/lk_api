<?php

namespace App\Services\Alipay;

use AlipayAop\AopCertClient;
use AlipayAop\request\AlipaySystemOauthTokenRequest;
use AlipayAop\request\AlipayTradeQueryRequest;
use AlipayAop\request\AlipayUserInfoAuthRequest;
use App\Models\UserAlipayAuthToken;
use Exception;

class AlipayCertService extends AlipayBaseService
{
    public function getRequest()
    {
    }
    
    /**
     * Description:
     *
     * @param array $data [
     *                    'uid'       => $uid,
     *                    'auth_code' => $auth_code,
     *                    'app_id'    => $app_id,
     *                    'source'    => $source,
     *                    'scope'     => $scope,
     *                    ]
     *
     * @author lidong<947714443@qq.com>
     * @date   2021/8/7 0007
     */
    public function saveUserAuthCode(array $data)
    {
        try {
            if (empty($data)) {
                throw new Exception('授权信息获取失败');
            }
            $uid = '';
            $auth_code = '';
            $app_id = '';
            $source = '';
            $scope = '';
            extract($data, EXTR_OVERWRITE);
            $UserAlipayAuthToken = new  UserAlipayAuthToken();
            $UserAlipayAuthToken->saveAuthCode($uid, $auth_code, $app_id, $source, $scope);
        } catch (Exception $e) {
        }
    }
    
    /**
     * Description:绑定用户信息
     *
     * @param $uid
     *
     * @return mixed
     * @throws \Exception
     * @author lidong<947714443@qq.com>
     * @date   2021/8/7 0007
     */
    public function userBinding($uid)
    {
        try {
            $auth_code = UserAlipayAuthToken::getUserAuthCode($uid);
            if (!$auth_code) {
                throw new Exception('授权信息获取失败');
            }
            $user_alipay_info = $this->getUserAccessTokenByAuthCode($auth_code);
            dd($user_alipay_info);
        } catch (Exception $e) {
            throw $e;
        }
        return $auth_code;
    }
    
    public function getUserAccessTokenByAuthCode($auth_code)
    {
        try {
            $AopCertClient = $this->getAopCertClient();
            $Request = new AlipaySystemOauthTokenRequest();
            $Request->setCode($auth_code);
            $Request->setGrantType('authorization_code');
            $Result = $AopCertClient->execute($Request);
            $responseNode = str_replace(".", "_", $Request->getApiMethodName())."_response";
//            dd($Request->$responseNode);
//            dd($responseNode);
            $resultCode = $Result->$responseNode->code;
            if (!empty($resultCode) && $resultCode != 10000) {
                echo "失败";
            } else {
                echo "成功";
            }
        } catch (Exception $e) {
            throw $e;
        }
        return $responseNode;
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
