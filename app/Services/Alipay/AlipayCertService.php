<?php

namespace App\Services\Alipay;

use AlipayAop\AopCertClient;
use AlipayAop\request\AlipaySystemOauthTokenRequest;
use AlipayAop\request\AlipayTradeQueryRequest;
use AlipayAop\request\AlipayUserInfoAuthRequest;
use AlipayAop\request\AlipayUserInfoShareRequest;
use App\Models\User;
use App\Models\UserAlipayAuthToken;
use Exception;
use Illuminate\Support\Facades\Log;

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
     * @throws \Exception
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
            return false;
        }
        return true;
    }
    
    /**
     * Description:绑定用户信息
     *
     * @param $uid
     *
     * @return bool
     * @throws \Exception
     * @author lidong<947714443@qq.com>
     * @date   2021/8/7 0007
     */
    public function userBinding($uid)
    {
        try {
            $auth_code = UserAlipayAuthToken::getUserAuthCode($uid);
            if (!$auth_code) {
                throw new Exception('授权码获取失败');
            }
            //code 换取 token
            $access_token_info = $this->getUserAccessTokenByAuthCode($auth_code);
            Log::debug('getUserAccessTokenByAuthCode-', [json_encode($access_token_info)]);
            $token_arr = json_decode(json_encode($access_token_info), true);
            $this->updateAccessToken($uid, $token_arr);
            // token 获取用户信息
            $user_info = $this->getUserInfoByAccessToken($access_token_info->access_token);
            $Users = User::findOrFail($uid);
            $Users->alipay_user_id = $user_info->user_id;
            $Users->alipay_nickname = $user_info->nick_name;
            $Users->alipay_avatar = $user_info->avatar;
            $Users->save();
        } catch (Exception $e) {
            Log::debug('Error:Alipay-AuthCode:'.$e->getMessage());
            throw $e;
            return false;
        }
        return true;
    }
    
    /**
     * Description:
     *
     * @param $uid
     *
     * @return bool
     * @author lidong<947714443@qq.com>
     * @date   2021/8/9 0009
     */
    public function userBindingCheck($uid)
    {
        try {
            $user = User::findOrFail($uid);
            if (!$user->alipay_user_id) {
                throw new Exception('alipay_user_id miss');
            }
        } catch (Exception $e) {
            return false;
        }
        return true;
    }
    
    /**
     * Description:通过auth_code换取access_token
     *
     * @param $auth_code
     *
     * @return string
     * @throws \Exception
     * @author lidong<947714443@qq.com>
     * @date   2021/8/9 0009
     */
    public function getUserAccessTokenByAuthCode($auth_code)
    {
        try {
            $AopCertClient = $this->getAopCertClient();
            $Request = new AlipaySystemOauthTokenRequest();
            $Request->setCode($auth_code);
            $Request->setGrantType('authorization_code');
            $Result = $AopCertClient->execute($Request);
            $responseNode = str_replace(".", "_", $Request->getApiMethodName())."_response";
            if (!isset($Result->$responseNode)) {
                throw new Exception('授权令牌获取失败'.json_encode($Result));
            }
        } catch (Exception $e) {
            Log::debug('Error:Alipay-AccessToken:'.$e->getMessage());
            throw $e;
        }
        /**
         * {
         * "alipay_system_oauth_token_response": {
         * "access_token": "authusrB60dec6d4f49f42269a82df627e4f3X28"
         * "alipay_user_id": "20880069462546073717281152811428"
         * "expires_in": 1296000
         * "re_expires_in": 2592000
         * "refresh_token": "authusrB9f61405980104b5fa7cb38eb9d950X28"
         * "user_id": "2088412397910280"
         * }
         * "alipay_cert_sn": "dc1bda7ed3c81023cf6e6e4e59a9b99e"
         * "sign": "H7Iw3gsgC/s5WAGoQmfnR2h6CotzbN67beEKLDbI6Ic3i0BoRbTRW9XkZfQ12nJZWH1SeHxaaJIf4ZWjCr8Ii8TjcYZtgbr0ZfY/yJd5g/saGT3UBCKF6/fDKy9Hg42eHfihR80G1DBTbR2zaXU3if7QBE09sCMXNoNzjsXJI04Fsr16YuFG3kMV9OZ4sFVu2mSOp1YDjalAdq01hyAzPy000V7KIPaxn/85VPOYNBsRnNRB7aZKHzVvinbuemDaS8x8wyFRM1WZlyz59Hxow5Q6UXB4E0HgmLCdUbtE6RW6sGA607g8NBfpK8Ni0aqCPbLvp5KgCYmX7rjxUyQ== "
         * }
         */
        return $Result->$responseNode;
    }
    
    /**
     * Description:更新accessTOKEN到数据库
     *
     * @param string                               $uid
     * @param array                                $access_token_arr
     * @param \App\Models\UserAlipayAuthToken|null $UserToken
     *
     * @throws \Exception
     * @author lidong<947714443@qq.com>
     * @date   2021/8/9 0009
     */
    public function updateAccessToken(string $uid, array $access_token_arr, UserAlipayAuthToken $UserToken = null)
    {
        try {
            if (empty($access_token_arr)) {
                throw new Exception('用户token信息为空');
            }
            if (empty($UserToken)) {
                $UserToken = UserAlipayAuthToken::whereUid($uid)->first();
            }
            $UserToken->access_token = $access_token_arr[ 'access_token' ];
            $UserToken->alipay_user_id = $access_token_arr[ 'user_id' ];
            $UserToken->expires_in = $access_token_arr[ 'expires_in' ];
            $UserToken->re_expires_in = $access_token_arr[ 're_expires_in' ];
            $UserToken->refresh_token = $access_token_arr[ 'refresh_token' ];
            $UserToken->alipay_alipay_user_id = $access_token_arr[ 'alipay_user_id' ];
            $UserToken->save();
        } catch (Exception $e) {
            throw $e;
        }
    }
    
    /**
     * Description:用accessTOKEN换取用户信息
     *
     * @param $access_token
     *
     * @return \$1|false|mixed|\SimpleXMLElement
     * @throws \Exception
     * @author lidong<947714443@qq.com>
     * @date   2021/8/9 0009
     */
    public function getUserInfoByAccessToken($access_token)
    {
        try {
            $AopCertClient = $this->getAopCertClient();
            $request = new AlipayUserInfoShareRequest();
            $result = $AopCertClient->execute($request, $access_token);
            $responseNode = str_replace(".", "_", $request->getApiMethodName())."_response";
            if (!isset($Result->$responseNode) || $Result->$responseNode->code != 10000) {
                throw new Exception('用户信息获取失败'.json_encode($result));
            }
        } catch (Exception $e) {
            Log::debug('Alipay-Error:'.$e->getMessage());
            throw  $e;
        }
        return $result->$responseNode;
    }
    
    
    /**
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     */
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
