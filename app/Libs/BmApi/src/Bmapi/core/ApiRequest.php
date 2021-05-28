<?php

namespace Bmapi\core;

use Bmapi\conf\Config;
use Bmapi\interfaces\ConfigInterface;
use Exception;

class ApiRequest
{

    const API_HOST = '';
//    const API_HOST = '';
    /**
     * APP_KEY
     * 注册之后从斑马力方客服处获取
     * 在 [Bmapi\conf\Config] 中配置
     *
     * @var string
     */
    protected $app_key;

    /**
     * APP_SECRET
     * 注册之后从斑马力方客服处获取
     * 在 [Bmapi\conf\Config] 中配置
     *
     * @var string
     */
    protected $app_secret;
    /**
     * 授权 Token
     * 登录 http://sale.bm001.com/
     * 从[http://sale.bm001.com/testtool/index] 页面获取
     *
     * 在 [Bmapi\conf\Config] 中配置
     *
     * @var
     */
    protected $access_token;
    /**
     * @var \Bmapi\conf\Config
     */
    protected $config;

    public function __construct(ConfigInterface $config = null)
    {
        if ($config == null) {
            $config = new Config();
        }
        $this->config = $config;
        $this->app_key = $config->getAppKey();
    }

    /**
     * post 请求
     *
     * @param string $url  请求链接
     * @param array  $data 推送的数据
     *
     * @return bool|string
     * @throws \Exception
     */
    public static function postRequest($url, $data = [])
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FAILONERROR, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        if (strlen($url) > 5 && strtolower(substr($url, 0, 5)) == "https") {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }
        // POST数据
        if (is_array($data) && count($data)) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        $output = curl_exec($ch);
        if (curl_errno($ch) > 0) {
            throw (new Exception(curl_error($ch)));
        } else {
            $http_status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if (200 !== $http_status_code) {
                throw new Exception($output, $http_status_code);
            }
        }
        curl_close($ch);
        return $output;
    }

}
